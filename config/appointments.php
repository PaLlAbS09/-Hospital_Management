<?php

return [

    /*
    |--------------------------------------------------------------------------
    | No-show grace period
    |--------------------------------------------------------------------------
    |
    | Minutes after an appointment slot start before an unchecked-in
    | Active appointment is treated as a no-show and auto-cancelled.
    | Set to 0 to cancel as soon as the slot time passes.
    |
    */

    'no_show_grace_minutes' => (int) env('APPOINTMENT_NO_SHOW_GRACE_MINUTES', 0),

];
