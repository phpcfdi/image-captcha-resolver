# CHANGELOG

Usamos [Versionado Semántico 2.0.0](SEMVER.md) por lo que puedes usar esta librería sin temor a romper tu aplicación.

Pueden aparecer cambios no liberados que se integran a la rama principal, pero no ameritan una nueva liberación de
versión, aunque sí su incorporación en la rama principal de trabajo. Generalmente, se tratan de cambios en el desarrollo.

## Versión 0.2.2 2022-11-16 *Happy pre-birthday Noni*

Este es una liberación de mantenimiento, el cambio más importante es la corrección de un posible problema
detectado por PHPStan.

### Cambios en entorno de desarrollo

- Se utiliza Phive en lugar del script `install-development-tools` para mantener las librerías de desarrollo.
- Se corrige el nombre del grupo de mantenedores en GitHub.
- En Github en el flujo de integración continua se actualiza para:
  - Incluir PHP 8.0, PHP 8.1 y PHP 8.2 a las pruebas.
  - Se divide el proceso de contrucción en varios pasos.
  - Se actualizan las acciones de GitHub a la versión 3.
- Se actualiza el año del archivo de licencia.
- Se utiliza un nuevo estándar de estilo de código basado en PSR-12, como los demás proyectos de *PhpCfdi*.
- Se agrega la integración con SonarCloud.
- Se elimina la integración con Scrutinizer CI. ¡Gracias Scrutinizer!

## Versión 0.2.1 2021-11-16 *Happy birthday Noni*

La versión más reciente de PHPStan `phpstan/phpstan:1.1.2` encontró algunos puntos de mejora
y uno que otro falso positivo. Se hacen las correcciones:

- `AntiCaptchaConnector`: Se previene un error de ejecución al verificar la respuesta del servidor.
- `CaptchaLocalResolverConnector`: Se previene un error de ejecución al verificar la respuesta del servidor.
- Se eliminan asignaciones superfluas al usar el operador `Null coalescing`.

## Versión 0.2.0 2021-07-28

Se agrega el resolvedor `CommandLineResolver` que pasa la imagen del captcha como un archivo temporal
para ser resuelto por un comando externo.

Se agrega el resolvedor `MultiResolver` que contiene un conjunto de resolvedores para intentar resolver
con ellos uno a uno.

Se cambia el nombre de la excepción `UnableToResolveCaptcha` a `UnableToResolveCaptchaException`.

Se cambia el nombre de la excepción `UndiscoverableClient` a `UndiscoverableClientException`.

Se agrega la clase interna `TemporaryFile` para crear y eliminar archivos temporales.

## Versión 0.1.0 2021-07-26

Versión inicial, implementa los siguientes resolvedores:

- Anti-Captcha: <https://anti-captcha.com>.
- CaptchaLocalResolver: <https://github.com/eclipxe13/captcha-local-resolver>.
- ConsoleResolver: Resolvedor en terminal.
- MockResolver: Resolvedor falso para pruebas.
