<?php

return [
    'api_key' => env('OPENAI_API_KEY'),
    'chat_model' => env('OPENAI_MODEL_CHAT', 'gpt-3.5-turbo'),
    'embedding_model' => env('OPENAI_MODEL_EMBEDDING', 'text-embedding-3-small'),
];
