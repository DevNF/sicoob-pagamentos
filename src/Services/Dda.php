<?php

namespace NFService\Sicoob\Services;

use NFService\Sicoob\Client\HttpClient;
use NFService\Sicoob\Sicoob;
use Valitron\Validator;

class Dda
{
    protected HttpClient $client;

    public function __construct(Sicoob $sicoob)
    {
        $this->client = $sicoob->getClient();
    }

    public function boletos(int $numeroConta, string $dataInicial, string $dataFinal, int $situacao, int $tipoData): string | GuzzleException | array | stdClass | null
    {
        $queryParameters = [
            'numeroConta' => $numeroConta,
            'dataInicial' => $dataInicial,
            'dataFinal' => $dataFinal,
            'situacao' => $situacao,
            'tipoData' => $tipoData
        ];

        $this->validarDadosRequisicao($queryParameters);

        return $this->client->requisicao('/boletos', 'GET', null,  $queryParameters);
    }
    
    private function validarDadosRequisicao(array $dados): void
    {
        $v = new Validator($dados);
        $v->rule('required', ['numeroConta', 'dataInicial', 'dataFinal', 'situacao', 'tipoData']);
        $v->rule('integer', ['numeroConta', 'situacao', 'tipoData']);
        $v->rule('date', ['dataInicial', 'dataFinal']);

        if(!$v->validate()) {
            $errors = $v->errors();
            foreach($errors as $key => $value) {
                $errors[$key] = implode(', ', $value);
            }
            throw new \Exception('Erro de validação: ' . implode(', ', $errors));
        }
    }
}