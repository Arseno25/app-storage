<?php

use Joaopaulolndev\FilamentGeneralSettings\Enums\TypeFieldEnum;

return [
    'show_application_tab' => true,
    'show_logo_and_favicon' => true,
    'show_analytics_tab' => true,
    'show_seo_tab' => true,
    'show_email_tab' => true,
    'show_social_networks_tab' => true,
    'expiration_cache_config_time' => 60,
    'show_custom_tabs'=> true,
    'custom_tabs' => [
        'more_configs' => [
            'label' => 'More Configs',
            'icon' => 'heroicon-o-plus-circle',
            'columns' => 1,
            'fields' => [
                'custom_field_1' => [
                    'type' => TypeFieldEnum::Text->value,
                    'label' => 'Custom Textfield 1',
                    'placeholder' => 'Custom Field 1',
                    'required' => true,
                    'rules' => 'required|string|max:255',
                ],
                ]
            ]
        ]
];
