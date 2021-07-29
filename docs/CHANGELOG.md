# CHANGELOG

## SemVer 2.0

Utilizamos [Versionado Semántico 2.0.0](SEMVER.md).

## Versión 0.2.0 2021-07-28

Se agrega el resolvedor `CommandLineResolver` que pasa la imagen del captcha como un archivo temporal
para ser resuelto por un commando externo.

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
