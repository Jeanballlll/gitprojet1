<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;





class Mail
{
    private $api_key = 'cf2ed1d263202b7e692960de94f6e689';
    private $api_key_secret = '7231496f7f2f0b162575764f76d1b577';

    public function send($to_email, $to_name, $subject, $content)
    {
        $mj = new Client($this->api_key, $this->api_key_secret, true,['version' => 'v3.1']);
$body = [
    'Messages' => [
        [
            'From' => [
                'Email' => "baljean91280@gmail.com",
                'Name' => "La Boutique JCB"
            ],
            'To' => [
                [
                    'Email' => $to_email,
                    'Name' =>  $to_name
                ]
            ],
            'TemplateID' => 3854600,
            'TemplateLanguage' => true,
            'Subject' => $subject,
            'Variables' => [
                'content' => $content,
                
            ]
        ]
        
    ]
];
$response = $mj->post(Resources::$Email, ['body' => $body]);
$response->success();
    }
}