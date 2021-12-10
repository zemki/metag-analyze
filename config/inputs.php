<?php

return [

    'available' =>
        [
            'text',
            'multiple choice',
            'one choice',
            'scale',
            'audio recording'
        ],
    'limit' => 10,
    'random' => 0,
    'example' => '[
	{
		"id": 0,
		"type": "text",
		"name": "Name of the book?"
		},
		{
			"id": 1,
			"type": "date",
			"name": "When you bought it?"
		}
	]'

];
