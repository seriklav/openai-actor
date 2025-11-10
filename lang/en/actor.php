<?php

return [
    'nav.actors' => 'Actors',

    'index' => [
        'title' => 'My Actor Requests',
        'headers' => [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'address' => 'Address',
            'gender' => 'Gender',
            'height_cm' => 'Height (cm)',
            'age' => 'Age',
        ],
        'empty' => 'No requests yet.'
    ],

    'form' => [
        'title' => 'Submit Actor Description',
        'email' => [
            'label' => 'Email',
            'placeholder' => 'john@example.com'
        ],
        'description' => [
            'label' => 'Description',
            'placeholder' => 'Describe the actor including full name and address...',
            'help' => 'Please enter your first name and last name, and also provide your address.',
        ],
        'submit' => 'Submit',
    ],

    'dash' => [
        'title' => 'Dashboard'
    ],

    'welcome' => [
        'title' => 'Welcome'
    ],

    'gender' => [
        'male'   => 'Male',
        'female' => 'Female',
        'other'  => 'Other',
    ],
];
