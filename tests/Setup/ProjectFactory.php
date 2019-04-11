<?php

namespace Tests\Setup;

use App\Project;
use App\Cases;
use App\User;

class ProjectFactory
{

	protected $casesCount = 0;
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

		factory(Cases::class,$this->casesCount)->create([
			'project_id' => $project->id
		]);

		return $project;
	}



}

