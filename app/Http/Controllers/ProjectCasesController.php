<?php

namespace App\Http\Controllers;

use App\Cases;
use App\CasesExport;
use App\Entry;
use App\Media;
use App\Project;
use App\QrLoginToken;
use App\User;
use Carbon\Carbon;
use Crypt;
use Exception;
use Helper;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectCasesController extends Controller
{
    protected const PROJECT = 'project';

    protected const MESSAGE = 'message';

    /**
     * @return Factory|\Illuminate\View\View
     */
    public function distinctshow(Project $project, Cases $case)
    {
        if (auth()->user()->notOwnerNorInvited($project) && ! auth()->user()->isAdmin()) {
            abort(403);
        }
        [$mediaValues, $availableMedia] = Cases::getMediaValues($case);
        [$availableInputs, $data] = Cases::getInputValues($case, $data);
        $data['entries']['list'] = Entry::where('case_id', '=', $case->id)->get();
        foreach ($data['entries']['list'] as $entry) {
            $entry['inputs'] = json_decode($entry['inputs']);
            $entry['media_id'] = Media::firstWhere('id', $entry['media_id'])->name;
        }
        $data['entries']['media'] = $mediaValues;
        $data['entries']['availablemedia'] = $availableMedia;
        //$data['entries']['inputs'] = $inputValues;
        $data['entries']['availableinputs'] = $availableInputs;
        $data['case'] = $case;
        $data['project'] = $project;
        $data = $this->getGraphBreadcrumb($project, $case, $data);

        return view('entries.distinctcases', $data);
    }

    public function groupedshow(Project $project, Cases $case)
    {
        if (auth()->user()->notOwnerNorInvited($project) && ! auth()->user()->isAdmin()) {
            abort(403);
        }
        $tempArray = Entry::where('case_id', '=', $case->id)->with('media')->get()->toArray();
        $data['entries'] = [];
        $data['availableInputs'] = $project->getAnswersInputs();
        //  $data['availableInputs'] = [];
        $textInputToUnset = [];
        foreach ($tempArray as $entry) {
            $tempInputsArray = json_decode($entry['inputs'], true);
            $inputEntry = [];

            $inputEntry['inputs'] = [];
            // format the inputs for the graph
            foreach ($tempInputsArray as $question => $answer) {
                if (is_array($answer)) {
                    foreach ($answer as $singularAnswer) {
                        $tempEntryInputs['id'] = $entry['id'];
                        $tempEntryInputs['name'] = $singularAnswer;
                        foreach ($data['availableInputs'] as $availableInput) {
                            if ($availableInput->name == $singularAnswer) {
                                $tempEntryInputs['color'] = $availableInput->color;
                                break;
                            }
                        }
                        array_push($inputEntry['inputs'], (object) $tempEntryInputs);
                    }
                } elseif (strlen($answer) > 1) {
                    //text & file
                    $tempEntryInputs['id'] = $entry['id'];
                    $tempEntryInputs['name'] = $question !== 'file' ? $answer : 'file';
                    foreach ($data['availableInputs'] as $key => $availableInput) {
                        if ($availableInput->name == $question) {
                            array_push($data['availableInputs'], (object) ['id' => count($data['availableInputs']) + 1, 'name' => $answer, 'color' => $availableInput->color]);
                            $tempEntryInputs['color'] = $availableInput->color;
                            array_push($textInputToUnset, $key);
                            break;
                        }
                    }
                    array_push($inputEntry['inputs'], (object) $tempEntryInputs);
                } else {
                    // scale
                    $tempEntryInputs['id'] = $entry['id'];
                    $tempEntryInputs['name'] = $answer;
                    foreach ($data['availableInputs'] as $availableInput) {
                        if ($availableInput->name == $answer) {
                            $tempEntryInputs['color'] = $availableInput->color;
                            break;
                        }
                    }
                    array_push($inputEntry['inputs'], (object) $tempEntryInputs);
                }
            }
            // translate the object to array
            // check here input type file and remark when there's a file - now it shows only the id of the file

            foreach ($entry as $key => $property) {
                if ($key == 'inputs') {
                    continue;
                }
                $inputEntry[$key] = $property;
            }
            $inputEntry['begin'] = strtotime($inputEntry['begin']);
            $inputEntry['end'] = strtotime($inputEntry['end']);
            $inputEntry['color'] = array_rand(array_flip(config('colors.chartCategories')), 1);
            array_push($data['entries'], $inputEntry);
        }
        $this->filterUnusedInputs($data, $textInputToUnset, $inputsToFilter);
        $this->assignColorsToMedia($case, $data);
        sort($data['availableInputs']);
        $data = $this->getGraphBreadcrumb($project, $case, $data);

        return view('entries.groupedcases', $data);
    }

    /**
     * Create a case belonging to a project
     *
     * @return Factory|\Illuminate\View\View
     */
    public function create(Project $project)
    {
        if (auth()->user()->notOwnerNorInvited($project)) {
            abort(403);
        }
        $data['breadcrumb'] = [
            url($project->path()) => strlen($project->name) > 20 ? substr($project->name, 0, 20) . '...' : $project->name,
            '#' => 'Create Case',
        ];
        $data['project'] = $project;

        return view('cases.create', $data);
    }

    private function createCases(Project $project)
    {
        $message = '';

        if (request('backendCase')) {
            $user = auth()->user();
            $case = $project->addCase(request('name'), 'value:0|days:0|lastDay:' . Carbon::now()->subDay());

            // Generate temp password for QR login
            $tempPassword = Helper::random_str(30);
            $case->forceFill([
                'temp_password' => Crypt::encryptString($tempPassword),
            ])->save();

            // Conditionally generate QR code
            if (request('generateQrCode')) {
                $this->autoGenerateQrCode($case, $tempPassword);
            }

            $message = __('backend case created.');

        } else {

            $emailInput = request('email');

            $emailArray = Helper::multiexplode([';', ',', ' '], $emailInput);
            $i = 0;
            foreach ($emailArray as $singleEmail) {
                $i++;
                if (request('sequentialNumbers')) {
                    $caseName = request('name') . ' ' . $i;
                } else {
                    $caseName = request('name');
                }

                if ((str_contains(request('name'), '{email}'))) {
                    $caseName = str_replace('{email}', $singleEmail, request('name'));
                }

                // Generate temp password for QR login
                $tempPassword = Helper::random_str(30);

                // Generate QR code data if checkbox is checked
                $qrCodeData = null;
                $qrCodeForEmail = null;

                if (request('generateQrCode')) {
                    // Use case duration for QR expiration
                    $durationDays = $this->extractDaysFromDuration(request('duration'));
                    $expiresAt = now()->addDays($durationDays);

                    $credentials = [
                        'email' => $singleEmail,
                        'password' => $tempPassword,
                        'expires_at' => $expiresAt->timestamp,
                    ];
                    $encryptedToken = Crypt::encryptString(json_encode($credentials));
                    $deepLinkUrl = config('app.url').'/qr-login?token='.urlencode($encryptedToken);

                    $qrCodeData = [
                        'qr_url' => $deepLinkUrl,
                        'encrypted_token' => $encryptedToken,
                        'expires_at' => $expiresAt,
                        'duration_days' => $durationDays,
                    ];

                    // Generate QR image only if sending via email (for performance)
                    if (request('sendQrCodeViaEmail')) {
                        $qrImage = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                            ->size(300)
                            ->margin(2)
                            ->generate($deepLinkUrl));

                        $qrCodeForEmail = array_merge($qrCodeData, [
                            'qr_image' => 'data:image/png;base64,'.$qrImage,
                        ]);
                    }
                }

                $user = User::createIfDoesNotExists(
                    User::firstOrNew(['email' => $singleEmail]),
                    request('sendanywayemail'),
                    request('sendanywayemailsubject'),
                    request('sendanywayemailmessage'),
                    $qrCodeForEmail
                );
                $case = $project->addCase($caseName, request('duration'));
                $case->addUser($user);

                $case->forceFill([
                    'temp_password' => Crypt::encryptString($tempPassword),
                ])->save();

                // Save QR token to database if generated
                if ($qrCodeData) {
                    // Regenerate encrypted credential with case_id now that case is created
                    $credentials = [
                        'email' => $singleEmail,
                        'password' => $tempPassword,
                        'case_id' => $case->id,
                        'expires_at' => $qrCodeData['expires_at']->timestamp,
                    ];
                    $encryptedToken = Crypt::encryptString(json_encode($credentials));

                    QrLoginToken::create([
                        'case_id' => $case->id,
                        'encrypted_credential' => $encryptedToken,
                        'expires_at' => $qrCodeData['expires_at'],
                        'notify_on_use' => false,
                        'created_by' => auth()->id(),
                    ]);
                }

                $message .= $user->email . " has been invited. \n";
            }

            foreach ($project->getInputs() as $object) {
                $hasFileUpload = property_exists($object, 'type') && $object->type === 'audio recording';
                if ($hasFileUpload) {
                    $encrypted = Crypt::encryptString(Helper::random_str(60));
                    $case->forceFill([
                        'file_token' => $encrypted,
                    ])->save();
                }
            }

        }

        return $message;
    }

    private function validateRequest(Project $project, $email)
    {

        if (auth()->user()->notOwnerNorInvited($project)) {
            abort(403);
        }

        if (request('backendCase')) {
            return;
        }
        request()->validate([
            'name' => 'required',
            'email' => 'required',
            'duration' => 'required',
        ]);

        $emails = Helper::multiexplode([';', ',', ' '], $email);

        $invalidEmails = [];

        foreach ($emails as $email) {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                array_push($invalidEmails, $email);
            }
        }

        if (! request('backendCase') && count($invalidEmails) > 0) {

            throw new \Exception(__('Not valid emails: ') . implode(',', $invalidEmails));
        }
    }

    public function store(Project $project)
    {

        try {

            $this->validateRequest($project, request('email'));

            $message = $this->createCases($project);

            return redirect($project->path())->with(['message' => $message, 'message_type' => 'success']);
        } catch (\Exception $e) {

            return redirect($project->path() . '/cases/new')
                ->withErrors(['message' => $e->getMessage()])
                ->withInput();
        }

    }

    /**
     * @return RedirectResponse|Redirector
     *
     * @throws AuthorizationException
     */
    public function update(Project $project, Cases $case)
    {
        $this->authorize('update', $case->project);
        request()->validate(['name' => 'required']);
        $case->update([
            'name' => request('name'),
        ]);

        return redirect($project->path());
    }

    /**
     * @return Application|Factory|View
     *
     * @throws Exception
     */
    public function destroy(Cases $case)
    {
        $project = $case->project;
        if ($project->created_by == auth()->user()->id) {
            $case->delete();
        } else {
            return response()->json(['message' => 'You can\'t delete this case'], 401);
        }

        return response()->json([self::MESSAGE => 'Case Deleted.'], 200);
    }

    /**
     * @return Response|BinaryFileResponse
     */
    public function export(Cases $case)
    {
        if (auth()->user()->notOwnerNorInvited($case->project)) {
            abort(403, __('you can\'t see the data of this project.'));
        }
        $headings = $this->getProjectInputHeadings($case->project);

        return (new CasesExport($case->id, $headings))->download('case.xlsx');
    }

    private function getProjectInputHeadings(Project $project): array
    {
        $headings = [];
        $inputs = json_decode($project->inputs);
        
        // Skip MART configuration object if present
        foreach ($inputs as $input) {
            // Skip MART configuration object
            if (property_exists($input, 'type') && $input->type === 'mart') {
                continue;
            }
            
            $isMultipleOrOneChoice = property_exists($input, 'numberofanswer') && $input->numberofanswer > 0;
            if ($isMultipleOrOneChoice) {
                for ($i = 0; $i < $input->numberofanswer; $i++) {
                    array_push($headings, $input->name);
                }
            } else {
                array_push($headings, $input->name);
            }
        }

        return $headings;
    }

    private function assignColorsToMedia(Cases $case, array &$data): void
    {
        /* $data['media'] = $case->entries()
                     ->leftJoin('media', 'entries.media_id', '=', 'media.id')
                     ->get()->unique(); */
        $tempArray = $case->entries()
            ->leftJoin('media', 'entries.media_id', '=', 'media.id')
            ->get()->unique()->toArray();
        $data['media'] = [];
        foreach ($tempArray as $media) {
            $mediaEntry = [];
            foreach ($media as $key => $property) {
                $mediaEntry[$key] = $property;
            }
            $mediaEntry['color'] = array_rand(array_flip(config('colors.chartCategories')), 1);
            array_push($data['media'], $mediaEntry);
        }
    }

    private function filterUnusedInputs(array &$data, array $textInputToUnset, &$inputsToFilter): void
    {
        $inputs = array_column($data['entries'], 'inputs');
        //  $inputs = array_map(function($o) { return $o['inputs']; }, $data['entries']);
        $inputs = array_map(function ($o) {
            $return = isset($o[0]) ? $o[0]->name : '';

            return $return;
        }, $inputs);
        // $inputsa = array_column($inputs, 'name');
        // dd($data['availableInputs']);
        $inputsToFilter = [];
        foreach ($data['availableInputs'] as $key => $availableInput) {
            if (! in_array($availableInput->name, $inputs)) {
                array_push($inputsToFilter, $key);
            }
        }
        foreach ($inputsToFilter as $key) {
            unset($data['availableInputs'][$key]);
        }
        foreach ($textInputToUnset as $key) {
            unset($data['availableInputs'][$key]);
        }
    }

    /**
     * @return mixed
     */
    private function getGraphBreadcrumb(Project $project, Cases $case, mixed $data)
    {
        if (env('APP_ENV') === 'production') {
            $slug = '/metag';
        } else {
            $slug = '';
        }
        $data['breadcrumb'] = [
            url('/') => 'Metag',

            $slug . $project->path() => $project->name,
            '#' => 'Graph',
        ];

        return $data;
    }

    /**
     * Extract number of days from duration string
     * Format: "value:X|days:Y|..." or "startDay:date|value:X|days:Y|..."
     *
     * @param string $duration
     * @return int Number of days, defaults to 30 if not found or backend case (0 days)
     */
    private function extractDaysFromDuration(string $duration): int
    {
        // Extract days value from duration string
        $daysMatch = [];
        if (preg_match('/days:(\d+)/', $duration, $daysMatch)) {
            $days = (int) $daysMatch[1];
            // For backend cases (0 days) or very short durations, use minimum of 30 days
            return max($days, 30);
        }

        // Default to 30 days if parsing fails
        return 30;
    }

    /**
     * Auto-generate QR code for a case
     *
     * @param Cases $case
     * @param string $tempPassword
     * @return void
     */
    private function autoGenerateQrCode(Cases $case, string $tempPassword): void
    {
        // Use case duration for expiration
        $durationDays = $this->extractDaysFromDuration($case->duration);
        $expiresAt = now()->addDays($durationDays);

        // Create credential payload
        $credentials = [
            'email' => $case->user->email,
            'password' => $tempPassword,
            'case_id' => $case->id,
            'expires_at' => $expiresAt->timestamp,
        ];

        // Encrypt credentials
        $encryptedToken = Crypt::encryptString(json_encode($credentials));

        // Create QR token record
        QrLoginToken::create([
            'case_id' => $case->id,
            'encrypted_credential' => $encryptedToken,
            'expires_at' => $expiresAt,
            'notify_on_use' => false,
            'created_by' => auth()->id(),
        ]);
    }
}
