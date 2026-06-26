<?php
/**
 * Lógica, funciones y punto de entrada de la aplicación.
 */

session_start();

const N_MIN = 2;
const N_MAX = 30;
const N_DEFAULT = 10;

/**
 * Calcula los primeros N términos de la secuencia de Fibonacci.
 *
 * @param int $n Cantidad de términos a generar (entre 2 y 30).
 * @return array Lista de enteros con la secuencia de Fibonacci.
 */
function calcular_fibonacci(int $n): array
{
    if ($n < 1) {
        return [];
    }

    if ($n === 1) {
        return [0];
    }

    $secuencia = [0, 1];

    for ($i = 2; $i < $n; $i++) {
        $secuencia[] = $secuencia[$i - 1] + $secuencia[$i - 2];
    }

    return $secuencia;
}

/**
 * Genera el HTML de barras proporcionales para cada término de la secuencia.
 *
 * @param array $secuencia Lista de valores numéricos de Fibonacci.
 * @return string Fragmento HTML con las barras del gráfico.
 */
function renderizar_barras(array $secuencia): string
{
    if (count($secuencia) === 0) {
        return '<p class="error">No hay datos para mostrar.</p>';
    }

    $maximo = max($secuencia);
    $html = '<div class="grafico">';

    foreach ($secuencia as $indice => $valor) {
        $porcentaje = $maximo > 0 ? ($valor / $maximo) * 100 : 0;
        $numeroTermino = $indice + 1;

        $html .= sprintf(
            '<div class="barra-contenedor" title="Término %d: %d">
                <div class="barra" style="height: %.2f%%;">
                    <span class="barra-valor">%d</span>
                </div>
                <span class="barra-etiqueta">#%d</span>
            </div>',
            $numeroTermino,
            $valor,
            $porcentaje,
            $valor,
            $numeroTermino
        );
    }

    $html .= '</div>';

    return $html;
}

/**
 * Obtiene el valor de N desde la sesión o devuelve el valor por defecto.
 *
 * @return int Último N guardado en sesión o N_DEFAULT si no existe.
 */
function obtener_n_de_sesion(): int
{
    if (isset($_SESSION['ultimo_n'])) {
        return (int) $_SESSION['ultimo_n'];
    }

    return N_DEFAULT;
}

/**
 * Valida que N esté dentro del rango permitido.
 *
 * @param mixed $n Valor recibido del formulario.
 * @return array{valido: bool, n: int|null, mensaje: string|null}
 */
function validar_n($n): array
{
    if ($n === null || $n === '') {
        return [
            'valido' => false,
            'n' => null,
            'mensaje' => 'Debes ingresar un valor numérico para N.',
        ];
    }

    if (!is_numeric($n) || (int) $n != $n) {
        return [
            'valido' => false,
            'n' => null,
            'mensaje' => 'N debe ser un número entero.',
        ];
    }

    $nEntero = (int) $n;

    if ($nEntero < N_MIN || $nEntero > N_MAX) {
        return [
            'valido' => false,
            'n' => null,
            'mensaje' => sprintf('N debe estar entre %d y %d.', N_MIN, N_MAX),
        ];
    }

    return [
        'valido' => true,
        'n' => $nEntero,
        'mensaje' => null,
    ];
}

$error = null;
$nActual = obtener_n_de_sesion();

// Si el formulario fue enviado por POST, validamos N y lo guardamos en sesión.
if (
    isset($_SERVER['REQUEST_METHOD'], $_POST['n'])
    && $_SERVER['REQUEST_METHOD'] === 'POST'
) {
    $validacion = validar_n($_POST['n']);

    if ($validacion['valido']) {
        $nActual = $validacion['n'];
        $_SESSION['ultimo_n'] = $nActual;
    } else {
        // Ante un valor inválido se muestra el error y se mantiene el N anterior.
        $error = $validacion['mensaje'];
    }
}

// Calcula la secuencia con el N actual (sesión, POST válido o valor por defecto).
$secuencia = calcular_fibonacci($nActual);
$tieneSesion = isset($_SESSION['ultimo_n']);

require __DIR__ . '/vista.php';
