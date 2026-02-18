<?php

return [


   'modules' => [
        'chatbot' => [
            'active' => true,
            'providers' => [
                Modules\ChatBot\Providers\ChatBotServiceProvider::class,
            ],
        ],
        'coupon' => [
            'active' => true,
            'providers' => [
                Modules\Coupon\Providers\CouponServiceProvider::class,
            ],
        ],
        'chatbot' => [
            'active' => true,
            'providers' => [
                Modules\Chatbot\Providers\ChatbotServiceProvider::class,
            ],
        ],
        'global-setting' => [
            'active' => true,
            'providers' => [
                Modules\GlobalSetting\Providers\GlobalSettingServiceProvider::class,
            ],
        ],
        'communication' => [
            'active' => true,
            'providers' => [
                Modules\Communication\Providers\CommunicationServiceProvider::class,
            ],
        ],
        'product' => [
            'active' => true,
            'providers' => [
                Modules\Product\Providers\ProductServiceProvider::class,
            ],
        ],
        'service' => [
            'active' => true,
            'providers' => [
                Modules\service\Providers\ServiceServiceProvider::class,
            ],
        ],
        'chat' => [
            'active' => true,
            'providers' => [
                Modules\Chat\Providers\ChatServiceProvider::class,
            ]
        ],
        'page' => [
            'active' => true,
            'providers' => [
                Modules\Page\Providers\PageServiceProvider::class,
            ],
        ],
        'leads' => [
            'active' => true,
            'providers' => [
                Modules\Leads\Providers\LeadsServiceProvider::class,
            ],
        ],
        'testimonials' => [
            'active' => true,
            'providers' => [
                Modules\Testimonials\Providers\TestimonialsServiceProvider::class,
            ],
        ],
        'faq' => [
            'active' => true,
            'providers' => [
                Modules\Faq\Providers\FaqServiceProvider::class,
            ],
        ],
        'newsletter' => [
            'active' => true,
            'providers' => [
                Modules\Newsletter\Providers\NewsletterServiceProvider::class,
            ],
        ],
        'blogs' => [
            'active' => true,
            'providers' => [
                Modules\Blogs\Providers\BlogsServiceProvider::class,
            ],
        ],
        'roles-permissions' => [
            'active' => true,
            'providers' => [
                Modules\RolesPermissions\Providers\RolesPermissionsServiceProvider::class,
            ],
        ],
        'menu-builder' => [
            'active' => true,
            'providers' => [
                Modules\MenuBuilder\Providers\MenuBuilderServiceProvider::class,
            ],
        ],
        'googlecalendarsync' => [
            'active' => true,
            'providers' => [
                Modules\GoogleCalendarSync\Providers\GoogleCalendarSyncServiceProvider::class,
            ],
        ],
    // Other modules...
    ],
];
