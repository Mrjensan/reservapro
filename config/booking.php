<?php

return [
    'business_hours' => [
        'start' => env('BOOKING_START', '09:00'),
        'end' => env('BOOKING_END', '18:00'),
        'days' => array_map('intval', array_filter(explode(',', env('BOOKING_DAYS', '1,2,3,4,5')))),
    ],
    'notification_email' => env('BOOKING_NOTIFICATION_EMAIL'),
];
