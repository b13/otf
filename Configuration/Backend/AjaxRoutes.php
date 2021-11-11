<?php

return [
    // Ajax endpoint for on-the-fly evaluation hints in FormEngine
    'form_otf' => [
        'path' => '/form/otf',
        'methods' => ['POST'],
        'target' => \B13\Otf\Backend\Controller\OtfAjaxController::class . '::processRequest',
    ],
];
