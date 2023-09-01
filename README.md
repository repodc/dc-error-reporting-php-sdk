# dc-error-reporting-php-sdk
SDK para integrar o sistema de alerta de erros com PHP

O sistema irá notificar os analistas da DC

## Instalação

   1. No composer.json do projeto, inclua a seguinte linha no objeto "require":
   ```jsonc
     "require": {
        ..., # outras dependencias do projeto
        "dc-tec/dc-error-reporting-php-sdk": "dev-main" # inclua essa linha
    },
   ```

   2. Agora adicione o objeto "repositories" logo abaixo do objeto "require" para adicionar a referência do repositório do SDK:
   ```jsonc
   "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/repodc/dc-error-reporting-php-sdk"
        }
    ],
   ```

   3. Finalmente rode o comando `composer update dc-tec/dc-error-reporting-php-sdk` para baixar a dependência

   Atenção: Para atualizar o código da dependência rode o mesmo comando acima
   

## Passo a Passo para Integrar com sistema em Laravel

1. Variáveis de ambiente (.env)

   É necessário ter as seguintes variáveis de ambiente configuradas:
   ```properties
   APP_ENV=homologation # Se o valor for "local" não será enviado as notificações
   DC_ERROR_REPORTING_TOKEN="token_de_acesso" # Pegue o token de acesso com o staff da DC
   ```

   **IMPORTANTE:** durante o desenvolvimento local sempre deixe o valor de **APP_ENV** igual à **"local"** para não enviar notificações ao ocorrer erros.
   
2. Configurar o token no arquivo **config/app.php**

   ```php
   [
     ..., # outras configurações,
     'dc_error_reporting_token' => env('DC_ERROR_REPORTING_TOKEN', ''),
   ]
   ```

3. Adicionar error handler no projeto

   No arquivo **app/Exceptions/Handler.php** do seu projeto use o método **send()** do SDK para reportar erros dentro do método **register()**:

   ```php
    use DcTec\DcErrorReportingPhpSdk\DcErrorReportingSdk;
   
    public function register(): void {
        $dcErrorReporting = new DcErrorReportingSdk('Nome do Sistema', config('app.env'), config('app.dc_error_reporting_token'));

        $this->reportable(function (Throwable $e) use ($dcErrorReporting) {
            $dcErrorReporting->send($e);
        });
    }
   ```

   Ao instanciar a classe **DcErrorReportingSdk** informe o nome do sistema corretamente no primeiro parâmetro

4. Caso haja a necessidade de reportar erros que estão contidos em um bloco de **try ... catch** basta usar a função helper do Laravel **report()**

   ```php
   try {
      //code...
   } catch (\Exception $e) {
       report($e);
   }
   ```

## Passo a Passo para Integrar com sistema em PHP puro

1. Variáveis de ambiente (.env)

   É necessário ter as seguintes variáveis de ambiente configuradas:
   ```properties
   APP_ENV=homologation # Se o valor for "local" não será enviado as notificações
   DC_ERROR_REPORTING_TOKEN="token_de_acesso" # Pegue o token de acesso com o staff da DC
   ```

   **IMPORTANTE:** durante o desenvolvimento local sempre deixe o valor de **APP_ENV** igual à **"local"** para não enviar notificações ao ocorrer erros.
   
2. Adicionar error handler personalizado no projeto

   Em um arquivo de escopo global (Ex: db.php ou config.php, varia de projeto para projeto), defina o seguinte error handler

   ```php
    use DcTec\DcErrorReportingPhpSdk\DcErrorReportingSdk;
   
    $dcErrorReporting = new DcErrorReportingSdk('Nome do Sistema', getenv('APP_ENV'), getenv('DC_ERROR_REPORTING_TOKEN'));

    set_exception_handler(function (\Throwable $e) use ($dcErrorReporting) {
        $dcErrorReporting->send($e);
    
        restore_exception_handler();
        throw $e;
    });
   ```

   Ao instanciar a classe **DcErrorReportingSdk** informe o nome do sistema corretamente no primeiro parâmetro

4. Caso haja a necessidade de reportar erros que estão contidos em um bloco de **try ... catch** basta utilizar o mesmo SDK

   ```php
     try {
       //código...
      } catch (\Exception $e) {
          $dcErrorReporting->send($e);
      
          //tratamento da exceção...
      }
   ```

#

E é isso, após configurado, qualquer erro / exceção não tratado pelo sistema será notificado aos analistas da DC

## Visualizar log de erros

Para acessar o log com os erros ocorridos, visite a url https://dc-error-reporting.dctec.dev/api/error_report/slug-sistema

Substitua slug-sistema pelo nome do sistema no formato slug (letras minúsculas, sem caracteres especiais e substituindo espaço por "-")

Exemplo: o nome do sistema é **Bablepet ERP (API)** então o slug ficará **bablepet-erp-api**
