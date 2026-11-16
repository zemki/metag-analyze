<?php

use App\Cases;
use App\Mart\MartSchedule;
use App\Project;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * One realistic MART study for checking the researcher's screens: a two-week
 * evening diary that asks every question type the app answers, except the
 * uploads (photo, audio, video, file).
 *
 * Six made-up participants with their own lives (student, parent in an
 * office job, shift nurse, retiree, freelancer, student athlete) answer on
 * 9 to 13 of the 14 evenings. Their answers hang together: little sleep and
 * stress lower the day, sport lifts it, workdays differ from days off, and
 * the optional questions are sometimes skipped. Some pick "other" and type
 * their own answer.
 *
 * The study ended a few days ago, so every case is completed and can be
 * consulted. Answers go through the app's submit code, as in
 * MartProjectSeeder. The cases belong to the owner, like the demo cases.
 *
 * It asks for the owner's email in a terminal; tests pass it in through
 * $ownerEmail. The account must exist: it never creates one.
 *
 * php artisan db:seed --class=\MartAllTypesStudySeeder
 */
class MartAllTypesStudySeeder extends MartProjectSeeder
{
    /** The owner's email, to give it without asking. */
    public ?string $ownerEmail = null;

    private const RANDOM_SEED = 20261116;

    private const STUDY_DAYS = 14;

    private const DAYS_SINCE_END = 4;

    private const INTRO = 'Good evening! A few questions about your day. It takes about two minutes.';

    private const DAY = 'How was your day overall? (1 = very bad, 10 = very good)';

    private const STRESS = 'How stressed did you feel today?';

    private const PLACE = 'Where did you spend most of your day?';

    private const COMPANY = 'Who did you spend time with today?';

    private const SLEEP = 'How many hours did you sleep last night?';

    private const SPORT = 'Did you do any sport or exercise today?';

    private const COFFEE = 'How many cups of coffee or tea did you have today?';

    private const WORD = 'Describe today in one word.';

    private const MOMENT = 'What was the most important moment of your day?';

    private const ENERGY = 'How much energy do you have left right now? (1 = none, 5 = plenty)';

    private const THANKS = 'Thank you! See you tomorrow evening.';

    private const STRESS_LEVELS = ['Not at all', 'A little', 'Moderately', 'Quite a bit', 'Extremely'];

    private const PLACES = ['At home', 'At work', 'At university', 'Out and about'];

    private const COMPANY_OPTIONS = ['Partner', 'Family', 'Friends', 'Colleagues or fellow students', 'Nobody'];

    private const WORDS = [
        'good' => ['Productive', 'Relaxed', 'Happy', 'Sunny', 'Fun', 'Grateful', 'Peaceful', 'Energising'],
        'ok' => ['Normal', 'Busy', 'Okay', 'Steady', 'Routine', 'Long', 'Quiet'],
        'bad' => ['Exhausting', 'Stressful', 'Draining', 'Chaotic', 'Frustrating', 'Tiring', 'Heavy'],
    ];

    /**
     * Who the participants are. "workdays": weekdays, shifts (three on, two
     * off), none, or flexible (weekdays and some weekends). Places and
     * company per workday and day off; ranges are [min, max]; stress is an
     * index into STRESS_LEVELS; sport and skipMoment are percentages.
     */
    private const PARTICIPANTS = [
        'P01' => [
            'workdays' => 'weekdays',
            'workPlaces' => ['At university' => 8, 'At home' => 2],
            'freePlaces' => ['At home' => 5, 'Out and about' => 4],
            'otherPlaces' => ['At the library', 'At my part-time job in a café'],
            'workCompany' => ['Colleagues or fellow students', 'Friends', 'Nobody'],
            'freeCompany' => ['Friends', 'Partner', 'Family'],
            'otherCompany' => ['My flatmates'],
            'sleep' => ['work' => [6, 8], 'free' => [8, 10]],
            'stress' => ['work' => [1, 3], 'free' => [0, 2]],
            'coffee' => [1, 4],
            'sport' => 30,
            'skipMoment' => 25,
            'hours' => [20, 22],
            'moments' => [
                'good' => ['My presentation in the seminar went really well.', 'Cooked dinner with my flatmates.', 'Got a good grade back on my statistics assignment.', 'Long walk by the river with a friend.'],
                'ok' => ['Spent most of the day reading for the seminar.', 'Lectures, library, home. Nothing special.', 'Video call with my parents.'],
                'bad' => ['Could not concentrate at all in the library.', 'The term paper is due soon and I am behind.', 'Argument with my flatmate about cleaning.'],
            ],
        ],
        'P02' => [
            'workdays' => 'weekdays',
            'workPlaces' => ['At work' => 7, 'At home' => 3],
            'freePlaces' => ['At home' => 6, 'Out and about' => 4],
            'otherPlaces' => ["At my in-laws' place", "At the kids' football match"],
            'workCompany' => ['Colleagues or fellow students', 'Family', 'Partner'],
            'freeCompany' => ['Family', 'Partner', 'Friends'],
            'otherCompany' => ['Neighbours'],
            'sleep' => ['work' => [5, 7], 'free' => [6, 8]],
            'stress' => ['work' => [1, 3], 'free' => [1, 2]],
            'coffee' => [2, 5],
            'sport' => 20,
            'skipMoment' => 35,
            'hours' => [20, 21],
            'moments' => [
                'good' => ['The kids and I baked a cake for grandma.', 'Finished the quarterly report a day early.', 'Quiet dinner with my partner after the kids were asleep.', 'Sunny afternoon at the playground with the family.'],
                'ok' => ['Back-to-back meetings, then the usual evening routine.', 'Home office day, laundry in between calls.', 'Drove the kids to swimming practice.'],
                'bad' => ['Our youngest was sick and I had to leave work early.', 'Two deadlines on the same day and a broken laptop.', 'The little one woke up three times last night.'],
            ],
        ],
        'P03' => [
            'workdays' => 'shifts',
            'workPlaces' => ['At work' => 10],
            'freePlaces' => ['At home' => 6, 'Out and about' => 3],
            'otherPlaces' => ["At my sister's place", 'At the allotment garden'],
            'workCompany' => ['Colleagues or fellow students'],
            'freeCompany' => ['Partner', 'Friends', 'Nobody', 'Family'],
            'otherCompany' => ['My neighbour'],
            // On a shift day, patients are always in the company too.
            'workOtherCompany' => 'Patients',
            'sleep' => ['work' => [5, 7], 'free' => [7, 10]],
            'stress' => ['work' => [2, 4], 'free' => [0, 1]],
            'coffee' => [2, 6],
            'sport' => 35,
            'skipMoment' => 20,
            'hours' => [21, 22],
            'moments' => [
                'good' => ['A patient I cared for all week went home today.', 'Calm shift, the team worked really well together.', 'Slept in and had a long breakfast on my day off.', 'Went climbing with friends.'],
                'ok' => ['Normal shift, lots of paperwork.', 'Errands and groceries on my day off.', 'Handover took longer than usual.'],
                'bad' => ['Two colleagues called in sick, we were short-staffed all shift.', 'Emergency on the ward right before the end of my shift.', 'Could not sleep after the late shift.'],
            ],
        ],
        'P04' => [
            'workdays' => 'none',
            'workPlaces' => [],
            'freePlaces' => ['At home' => 6, 'Out and about' => 4],
            'otherPlaces' => ['In the garden', "At my daughter's"],
            'workCompany' => [],
            'freeCompany' => ['Partner', 'Family', 'Friends', 'Nobody'],
            'otherCompany' => ['My grandchildren', 'The choir'],
            'sleep' => ['work' => [6, 8], 'free' => [6, 8]],
            'stress' => ['work' => [0, 1], 'free' => [0, 1]],
            'coffee' => [0, 3],
            'sport' => 50,
            'skipMoment' => 10,
            'hours' => [19, 20],
            'moments' => [
                'good' => ['The grandchildren visited and we played cards all afternoon.', 'First tomatoes from the garden this year.', 'Choir rehearsal, we finally got the difficult piece right.', 'Long bike ride along the river with my partner.'],
                'ok' => ["Doctor's appointment in the morning, otherwise quiet.", 'Read the newspaper and did some gardening.', 'Phoned my sister for an hour.'],
                'bad' => ['My knee hurt all day, so no walk.', 'Felt a bit lonely, nobody called.', 'Trouble with the online banking, very frustrating.'],
            ],
        ],
        'P05' => [
            'workdays' => 'flexible',
            'workPlaces' => ['At home' => 7, 'Out and about' => 3],
            'freePlaces' => ['At home' => 4, 'Out and about' => 6],
            'otherPlaces' => ['At a co-working space', "At a client's office"],
            'workCompany' => ['Nobody', 'Colleagues or fellow students', 'Partner'],
            'freeCompany' => ['Friends', 'Partner', 'Family'],
            'otherCompany' => ['Clients'],
            'sleep' => ['work' => [6, 8], 'free' => [7, 9]],
            'stress' => ['work' => [1, 4], 'free' => [0, 2]],
            'coffee' => [2, 6],
            'sport' => 40,
            'skipMoment' => 25,
            'hours' => [19, 21],
            'moments' => [
                'good' => ['The client approved the final design without changes.', 'Landed a new project for next month.', 'Yoga in the morning and a really focused work day.', 'Dinner with old friends in town.'],
                'ok' => ['Invoices and emails most of the day.', 'Worked from a café for a change.', 'Small revisions for two clients.'],
                'bad' => ['A client still has not paid an invoice from two months ago.', 'My laptop crashed and I lost half a day of work.', 'Too many small jobs at once, no time to think.'],
            ],
        ],
        'P06' => [
            'workdays' => 'weekdays',
            'workPlaces' => ['At university' => 6, 'Out and about' => 2],
            'freePlaces' => ['Out and about' => 5, 'At home' => 4],
            'otherPlaces' => ['At the sports hall', 'At a tournament'],
            'workCompany' => ['Colleagues or fellow students', 'Friends'],
            'freeCompany' => ['Friends', 'Family', 'Partner'],
            'otherCompany' => ['My team', 'My coach'],
            'sleep' => ['work' => [7, 9], 'free' => [8, 10]],
            'stress' => ['work' => [1, 3], 'free' => [0, 2]],
            'coffee' => [0, 2],
            'sport' => 85,
            'skipMoment' => 30,
            'hours' => [20, 22],
            'moments' => [
                'good' => ['We won our match 3:1.', 'New personal best in training.', 'Team dinner after practice.', 'Passed my anatomy exam.'],
                'ok' => ['Lectures in the morning, training in the evening.', 'Recovery day, mostly stretching and studying.', 'Physio appointment for my shoulder.'],
                'bad' => ['Lost the match in the last minutes.', 'Training and studying, too little time for both.', 'Pulled a muscle in practice.'],
            ],
        ],
    ];

    public function run()
    {
        $owner = $this->studyOwner();

        $lastDay = Carbon::today()->subDays(self::DAYS_SINCE_END);
        $firstDay = $lastDay->copy()->subDays(self::STUDY_DAYS - 1);

        mt_srand(self::RANDOM_SEED);

        try {
            $project = $this->createProject($owner, 'Everyday Life & Wellbeing',
                'A two-week evening diary on how people feel, sleep, spend their day and with whom.',
                'Evening check-in',
                [[
                    'name' => 'Welcome',
                    'content' => '<h1>Everyday Life & Wellbeing</h1><p>For the next two weeks you will get one short questionnaire every evening between 7 and 11 pm.</p><p>Answer for the day that is ending. If you miss an evening, just carry on the next day.</p>',
                    'buttonText' => 'Start',
                    'showOnFirstAppStart' => true,
                    'sortOrder' => 0,
                ]],
            );

            $schedule = $this->createEveningSchedule($project, $firstDay, $lastDay);
            $questions = $schedule->questions()->get();

            $submits = 0;
            foreach (self::PARTICIPANTS as $name => $profile) {
                $submits += $this->createParticipant($project, $schedule, $questions, $name, $profile, $firstDay, $lastDay);
            }
        } finally {
            mt_srand();
        }

        $this->command->info("Created \"{$project->name}\" for {$owner->email}: " . count(self::PARTICIPANTS) . " completed cases, {$submits} evening check-ins.");
    }

    /**
     * The existing account with the given or typed email. In a terminal a
     * wrong email is asked again; without one there's no email, so it stops.
     */
    private function studyOwner(): User
    {
        $email = $this->ownerEmail ?? $this->command->ask('Email of the account that will own the study');

        while (true) {
            $email = trim((string) $email);
            if ($email === '') {
                throw new RuntimeException('MartAllTypesStudySeeder needs the email of an existing account, so run it in a terminal.');
            }

            $owner = User::where('email', $email)->first();
            if ($owner) {
                $this->command->info("The study will belong to {$owner->email} (ID: {$owner->id}).");

                return $owner;
            }

            if ($this->ownerEmail !== null) {
                throw new RuntimeException("No account with the email {$email}.");
            }
            $this->command->error("No account with the email {$email}. Type another one, or register it first.");
            $email = $this->command->ask('Email of the account that will own the study');
        }
    }

    /**
     * Questionnaire 1, once an evening during the study, with every question
     * type the app answers except uploads.
     */
    private function createEveningSchedule(Project $project, Carbon $firstDay, Carbon $lastDay): MartSchedule
    {
        $schedule = MartSchedule::create([
            'mart_project_id' => $project->martProject()->id,
            'questionnaire_id' => 1,
            'name' => 'Evening check-in',
            'type' => 'repeating',
            'timing_config' => [
                'start_date_time' => ['date' => $firstDay->format('Y-m-d'), 'time' => '19:00'],
                'end_date_time' => ['date' => $lastDay->format('Y-m-d'), 'time' => '23:00'],
                'daily_interval_duration' => 4,
                'min_break_between' => 60,
                'max_daily_submits' => 1,
                'daily_start_time' => '19:00',
                'daily_end_time' => '23:00',
                'quest_available_at' => 'startOfInterval',
            ],
            'notification_config' => [
                'show_progress_bar' => true,
                'show_notifications' => true,
                'notification_text' => 'Time for your evening check-in',
            ],
        ]);

        $other = fn (string $label) => ['includeOtherOption' => true, 'otherOptionLabel' => $label, 'otherTextType' => 'text'];

        $this->createQuestionsForSchedule($schedule, [
            ['text' => self::INTRO, 'type' => 'display', 'mandatory' => false, 'config' => []],
            ['text' => self::DAY, 'type' => 'range', 'mandatory' => true, 'config' => ['min' => 1, 'max' => 10, 'step' => 1]],
            ['text' => self::STRESS, 'type' => 'range values', 'mandatory' => true, 'config' => ['options' => self::STRESS_LEVELS]],
            ['text' => self::PLACE, 'type' => 'one choice', 'mandatory' => true, 'config' => ['options' => self::PLACES] + $other('Somewhere else')],
            ['text' => self::COMPANY, 'type' => 'multiple choice', 'mandatory' => true, 'config' => ['options' => self::COMPANY_OPTIONS] + $other('Someone else')],
            ['text' => self::SLEEP, 'type' => 'number', 'mandatory' => true, 'config' => ['min' => 0, 'max' => 14, 'maxDigits' => 2]],
            ['text' => self::SPORT, 'type' => 'one choice', 'mandatory' => true, 'config' => ['options' => ['Yes', 'No']]],
            ['text' => self::COFFEE, 'type' => 'number', 'mandatory' => false, 'config' => ['min' => 0, 'max' => 15, 'maxDigits' => 2]],
            ['text' => self::WORD, 'type' => 'text', 'mandatory' => true, 'config' => ['placeholder' => 'e.g. relaxed']],
            ['text' => self::MOMENT, 'type' => 'textarea', 'mandatory' => false, 'config' => ['placeholder' => 'A sentence or two is enough']],
            ['text' => self::ENERGY, 'type' => 'range', 'mandatory' => true, 'config' => ['min' => 1, 'max' => 5, 'step' => 1]],
            ['text' => self::THANKS, 'type' => 'display', 'mandatory' => false, 'config' => []],
        ]);

        return $schedule;
    }

    /**
     * A participant's case, their evening check-ins while it's open, and then
     * the study's dates, which complete it. Returns the number of check-ins.
     */
    private function createParticipant(Project $project, MartSchedule $schedule, Collection $questions, string $name, array $profile, Carbon $firstDay, Carbon $lastDay): int
    {
        $case = Cases::create([
            'project_id' => $project->id,
            'user_id' => $project->created_by,
            'name' => $name,
            'duration' => 'startDay:' . $firstDay->format('d.m.Y') . '|',
            'created_at' => $firstDay,
        ]);

        $days = range(0, self::STUDY_DAYS - 1);
        shuffle($days);
        $days = array_slice($days, 0, mt_rand(9, 13));
        sort($days);

        $moments = [];
        foreach ($days as $day) {
            $date = $firstDay->copy()->addDays($day);
            $startedAt = $date->copy()->setTime(mt_rand(...$profile['hours']), mt_rand(0, 50), mt_rand(0, 59));
            $inputs = $this->evening($profile, $this->isWorkday($profile['workdays'], $date, $day), $moments);
            // Longer when they write about their day.
            $seconds = mt_rand(55, 130) + ($inputs[self::MOMENT] === '' ? 0 : mt_rand(25, 70));

            $this->submit($case, $schedule, $questions, $inputs, $startedAt, $seconds);
        }

        $case->update([
            'duration' => 'startDay:' . $firstDay->format('d.m.Y') . '|lastDay:' . $lastDay->format('d.m.Y') . '|',
            'first_login_at' => $firstDay->copy()->setTime(mt_rand(9, 18), mt_rand(0, 59)),
        ]);

        return count($days);
    }

    private function isWorkday(string $workdays, Carbon $date, int $day): bool
    {
        return match ($workdays) {
            'weekdays' => ! $date->isWeekend(),
            'shifts' => $day % 5 < 3,
            'flexible' => ! $date->isWeekend() || mt_rand(1, 4) === 1,
            default => false,
        };
    }

    /**
     * One evening's answers, keyed by question text.
     *
     * @param  array<string, string[]>  $moments  what's left of each mood's moments, so they don't repeat soon
     */
    private function evening(array $profile, bool $workday, array &$moments): array
    {
        $kind = $workday ? 'work' : 'free';
        $sleep = mt_rand(...$profile['sleep'][$kind]);
        $stress = mt_rand(...$profile['stress'][$kind]);
        $sport = mt_rand(1, 100) <= $profile['sport'];

        $rating = 6 + ($sleep >= 7 ? 1 : ($sleep <= 5 ? -1 : 0)) - ($stress - 1) + ($sport ? 1 : 0) + ($workday ? 0 : 1) + mt_rand(-1, 1);
        $rating = max(1, min(10, $rating));
        $mood = $rating >= 8 ? 'good' : ($rating >= 5 ? 'ok' : 'bad');

        $energy = (int) round(($sleep - 4) * 0.8) - ($stress >= 3 ? 1 : 0) + mt_rand(-1, 1);
        $coffee = mt_rand(...$profile['coffee']) + ($workday && $sleep <= 6 ? 1 : 0);

        $moment = '';
        if (mt_rand(1, 100) > $profile['skipMoment']) {
            if (empty($moments[$mood])) {
                $moments[$mood] = $profile['moments'][$mood];
                shuffle($moments[$mood]);
            }
            $moment = array_shift($moments[$mood]);
        }

        return [
            self::DAY => $rating,
            self::STRESS => self::STRESS_LEVELS[$stress],
            self::PLACE => $this->place($profile, $kind),
            self::COMPANY => $this->company($profile, $kind),
            self::SLEEP => $sleep,
            self::SPORT => $sport ? 'Yes' : 'No',
            // 0 cups is left out, as the app drops a number of 0.
            self::COFFEE => $coffee,
            self::WORD => $this->pick(self::WORDS[$mood]),
            self::MOMENT => $moment,
            self::ENERGY => max(1, min(5, $energy)),
        ];
    }

    /**
     * A place from the profile, now and then one they type themselves.
     */
    private function place(array $profile, string $kind)
    {
        if (mt_rand(1, 100) <= 12) {
            return ['other' => $this->pick($profile['otherPlaces'])];
        }

        return $this->weighted($profile[$kind . 'Places']);
    }

    /**
     * One or two kinds of company, "Nobody" alone, and now and then someone
     * they name themselves.
     */
    private function company(array $profile, string $kind): array
    {
        $options = $profile[$kind . 'Company'];
        shuffle($options);
        $company = array_slice($options, 0, mt_rand(1, min(2, count($options))));
        if (in_array('Nobody', $company, true)) {
            $company = ['Nobody'];
        }

        if ($kind === 'work' && isset($profile['workOtherCompany'])) {
            $company[] = ['other' => $profile['workOtherCompany']];
        } elseif ($company !== ['Nobody'] && mt_rand(1, 100) <= 15) {
            $company[] = ['other' => $this->pick($profile['otherCompany'])];
        }

        return $company;
    }

    private function weighted(array $weights): string
    {
        $roll = mt_rand(1, array_sum($weights));
        foreach ($weights as $label => $weight) {
            $roll -= $weight;
            if ($roll <= 0) {
                return $label;
            }
        }

        return array_key_last($weights);
    }

    private function pick(array $items)
    {
        return $items[mt_rand(0, count($items) - 1)];
    }
}
