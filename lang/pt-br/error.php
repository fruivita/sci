<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HTTP Errors Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the Http ErrosV Views. Feel free to tweak each of these messages here.
    |
    */

    '401' => [
        'title' => 'Acesso Não Autorizado',
        'message' => 'Ooops!!! Suas credencias se perderam, tente se autenticar novamente.'
    ],
    '403' => [
        'title' => 'Acesso Proibido',
        'message' => 'Ooops!!! Esse recurso não está disponível para você. Procure um administrador.'
    ],
    '404' => [
        'title' => 'Página Não Encontrada',
        'message' => 'Ooops!!! Essa página não existe. Verifique a URL digitada.'
    ],
    '405' => [
        'title' => 'Método não Permitido',
        'message' => 'Ooops!!! Esse método não é permitido para essa URL.'
    ],
    '419' => [
        'title' => 'Página Expirada',
        'message' => 'Ooops!!! A sua requisição expirou. Se persistir, procure um administrador.'
    ],
    '429' => [
        'title' => 'Requisições em Excesso',
        'message' => 'Ooops!!! Você fez mais requisições por segundo que o permitido pela aplicação.'
    ],
    '4xx' => [
        'title' => 'Erro no cliente',
        'message' => 'Ooops!!! Parece haver algum problema com sua requisição. Se persistir, procure um administrador.'
    ],
    '500' => [
        'title' => 'Erro Interno',
        'message' => 'Ooops!!! Salve-se quem puder, pois o servidor está com problemas graves. Procure um administrador.'
    ],
    '503' => [
        'title' => 'Serviço Indisponível',
        'message' => 'Ooops!!! Os serviços estão indisponíveis. Tente novamente mais tarde.'
    ],
    '5xx' => [
        'title' => 'Erro no servidor',
        'message' => 'Ooops!!! O servidor está tendo problemas internos para processar sua requisição. Procure um administrador.'
    ],
];
