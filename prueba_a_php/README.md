# Prueba A — Fibonacci Interactivo (PHP)

Script PHP que calcula y muestra la secuencia de Fibonacci en una página HTML interactiva. El último valor de **N** ingresado se persiste en sesión PHP entre recargas.

## Requisitos

- PHP 7.4 o superior (probado con PHP 8.x)
- Extensión de sesiones habilitada (incluida por defecto)

## Instalación

1. Clona el repositorio:

```bash
git clone <url-del-repositorio>
cd prueba_a_php
```

2. No se requieren dependencias externas ni variables de entorno.

## Estructura del proyecto

```
prueba_a_php/
├── index.php   # Lógica PHP, funciones y control de sesión
├── vista.php   # Plantilla HTML de la página
└── README.md
```

## Ejecución

Desde la carpeta `prueba_a_php`, inicia el servidor embebido de PHP:

```bash
php -S localhost:8000
```

Abre en el navegador:

```
http://localhost:8000/index.php
```

## Uso

1. Al cargar la página por primera vez, se muestra la secuencia con **N = 10** (valor por defecto).
2. Ingresa un valor entre **2** y **30** en el formulario y haz clic en **Calcular**.
3. La secuencia y el gráfico de barras se actualizan automáticamente.
4. Al recargar la página sin enviar el formulario, se conserva el último **N** guardado en `$_SESSION['ultimo_n']`.

## Funciones principales

| Función | Descripción |
|---------|-------------|
| `calcular_fibonacci($n)` | Genera los primeros N términos de Fibonacci |
| `renderizar_barras($secuencia)` | Devuelve HTML con barras proporcionales al valor máximo |
| `obtener_n_de_sesion()` | Recupera el último N de la sesión o el valor por defecto |

## Validación

- **N** debe ser un entero entre 2 y 30.
- Valores inválidos muestran un mensaje de error sin interrumpir la página; se mantiene el último valor válido de sesión.

## Variables de configuración

Este proyecto no utiliza variables de entorno. Las constantes definidas en `index.php` son:

| Constante | Valor | Descripción |
|-----------|-------|-------------|
| `N_MIN` | 2 | Mínimo de términos permitidos |
| `N_MAX` | 30 | Máximo de términos permitidos |
| `N_DEFAULT` | 10 | Valor inicial cuando no hay sesión |
