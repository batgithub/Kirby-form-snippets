<?php

return [
    'debug' => true,
    'uniform.honeytime.key' => 'base64:m9pAO+r/7SbyT0lfWTYM4+iV9BwZiT3ouxBurDoNAXs=',
    'baptiste.kirby-form-snippets' => [
        'defaultEmailTo' => 'test@example.com',
        'honeytime' => [
            'enabled' => false,
            'seconds' => 10,
        ],
        'forms' => [
            'contact' => [
                'title' => 'Contact',
                'fields' => [
                    'name' => [
                        'input' => 'input',
                        'label' => 'Nom',
                        'required' => true,
                    ],
                    'email' => [
                        'input' => 'input',
                        'type' => 'email',
                        'label' => 'Email',
                        'required' => true,
                    ],
                    'message' => [
                        'input' => 'textarea',
                        'label' => 'Message',
                        'required' => true,
                    ],
                    'website' => [
                        'input' => 'honeypot',
                    ],
                ],
                'email' => [
                    'to' => 'contact@example.com',
                    'from' => 'noreply@example.com',
                    'subject' => 'Nouveau message',
                ],
            ],
            'honeytime' => [
                'honeytime' => true,
                'fields' => [
                    'email' => [
                        'input' => 'input',
                        'type' => 'email',
                        'label' => 'Email',
                        'required' => true,
                    ],
                ],
                'email' => [
                    'to' => 'contact@example.com',
                    'from' => 'noreply@example.com',
                    'subject' => 'Honeytime test',
                ],
            ],
            'filter' => [
                'mode' => 'filter',
                'fields' => [
                    'q' => [
                        'input' => 'input',
                        'label' => 'Recherche',
                    ],
                    'website' => [
                        'input' => 'honeypot',
                    ],
                ],
            ],
            'options' => [
                'fields' => [
                    'topic' => [
                        'input' => 'select',
                        'label' => 'Sujet',
                        'required' => true,
                        'options' => [
                            ['label' => 'Support', 'value' => 'support'],
                            ['label' => 'Ventes', 'value' => 'sales'],
                        ],
                    ],
                    'tags' => [
                        'input' => 'checkbox-group',
                        'label' => 'Tags',
                        'required' => true,
                        'options' => [
                            ['label' => 'PHP', 'value' => 'php'],
                            ['label' => 'Kirby', 'value' => 'kirby'],
                        ],
                    ],
                    'plan' => [
                        'input' => 'radio-group',
                        'label' => 'Plan',
                        'required' => true,
                        'options' => [
                            ['label' => 'Basic', 'value' => 'basic'],
                            ['label' => 'Pro', 'value' => 'pro'],
                        ],
                    ],
                    'consent' => [
                        'input' => 'checkbox',
                        'label' => 'Consentement',
                        'required' => true,
                    ],
                ],
            ],
        ],
    ],
];
