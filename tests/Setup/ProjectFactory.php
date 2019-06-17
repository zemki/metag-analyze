<?php

namespace Tests\Setup;

use App\Project;
use App\Cases;
use \App\Media;
use \App\Place;
use App\User;
use App\Communication_Partner;

class ProjectFactory
{

	protected $casesCount = 0;
	protected $mediaCount = 0;
	protected $placeCount = 0;
	protected $communicationpartnerCount = 0;
	protected $user;
	protected $inputs;

	public function withCases($count)
	{
		$this->casesCount = $count;

		return $this;
	}

	public function withInputs($inputs)
	{
		$this->inputs = $inputs;

		return $this;
	}

	public function createdBy($user)
	{
		$this->user = $user;

		return $this;
	}

	public function create()
	{
		$project = factory(Project::class)->create([
			'created_by' => $this->user ?? factory(User::class),
			'inputs' => $this->inputs ?? ''
		]);

/*		$project->media()->sync(factory(Media::class,$this->mediaCount)->create());
		$project->places()->sync(factory(Place::class,$this->placeCount)->create());
		$project->communication_partners()->sync(factory(Communication_Partner::class,$this->communicationpartnerCount)->create());*/

		factory(Cases::class,$this->casesCount)->create([
			'project_id' => $project->id,
			'user_id' =>  $project->created_by ?? factory(User::class)
		]);

		return $project;
	}



}

