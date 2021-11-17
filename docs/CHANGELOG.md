# CHANGELOG

Usamos [Versionado Semántico 2.0.0](SEMVER.md) por lo que puedes usar esta librería sin temor a romper tu aplicación.

Pueden aparecer cambios no liberados que se integran a la rama principal, pero no ameritan una nueva liberación de
versión, aunque sí su incorporación en la rama principal de trabajo. Generalmente se tratan de cambios en el desarrollo.

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
