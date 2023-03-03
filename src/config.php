<?php
require  '../vendor/autoload.php'; // remove this line if you use a PHP Framework.

use Orhanerday\OpenAi\OpenAi;


function completion($message, $open_ai_key=null, $in_prompt=null, $transcripts=null){

    try {

        $open_ai = new OpenAi($open_ai_key);

        $history = "";
        if($transcripts){
            foreach ($transcripts as $transcript) {
                $history .= "\nHuman:" . $transcript['message'] . "\n";
            }
        }

        if($in_prompt){
            $initial_prompt = jak_string_encrypt_decrypt($in_prompt, false);
        } else {
            $initial_prompt = "The following is a conversation with an AI assistant." .
            "The assistant is helpful, creative, clever, and very friendly." .
            "The assistant is to make life easier and answer any questions human might have. Ask AI for anything, and it will do best to provide a helpful response. The AI keeps all the context of the conversation and could analyze all the chat for any particular piece of information that was shared by Human " .
            "\n\nHuman: Hello, who are you?\nAI: I am an AI created by OpenAI. How can I help you today?";
        }

        $prompt = $initial_prompt .
        $history .
        "\nHuman:" . $message . "\nAI:";

        $complete = $open_ai->completion([
            'model' => 'text-davinci-003',
            'prompt' => $prompt,
            'temperature' => 0.9,
            'max_tokens' => 150,
            'frequency_penalty' => 0,
            'presence_penalty' => 0.6
        ]);
        $resp = json_decode($complete, true);
        return $resp["choices"][0]["text"];

    } catch(Exception $e) {
        return false;
    }

}