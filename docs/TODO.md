# phpcfdi/image-captcha-resolver To Do List

## Tareas pendientes

### Constructor desde entorno o configuración

Poder construir el resolvedor utilizando configuraciones de entorno, por ejemplo:

```dotenv
ICR_SERVICE="anticaptcha"
ICR_ANTICAPTCHA_CLIENT_KEY="0123..."
ICR_INITIAL_WAIT=3
ICR_TIMEOUT=120
ICR_SLEEP=1000
```

O un arreglo o archivo de configuración:

```php
return [
    '@service' => 'anti-captcha', // one of the keys
    '@defaults' => [ // merge with service configurations
        'initial' => 5,
        'timeout' => 120,
        'sleep' => 2000,
    ],
    'anti-captcha' => [ // driver setup
        'client_key' => '0123...'
    ],
    'captcha-local-resolver' => [ // driver setup
        'base_url' => 'http://localhost:9095',
        'initial' => 10,
        'sleep' => 500,
    ],
];
```

### Liberar la primera versión estable

Después de experimentar con este proyecto y sus versiones `0.x` liberar una primera versión.

## Posibles tareas

Ideas de lo que se puede hacer en el proyecto.

### Implementación de `tesseract`

En pruebas de campo con la imagen de docker `clearlinux/tesseract-ocr` he podido resolver satisfactoriamente
algunos captchas simples del SAT.
Primero se tiene que convertir el captcha en una imagen simple a blanco y negro eliminando el ruido de fondo.
Después se le pide a `tesseract` que la convierta.

## Descartadas

Ideas descartadas.
