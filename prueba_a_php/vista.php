<?php
/**
 * Vista HTML de la aplicación Fibonacci.
 * Recibe desde index.php: $nActual, $secuencia, $error y $tieneSesion.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fibonacci Interactivo</title>
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1rem;
            background: #f5f7fa;
            color: #1f2933;
        }

        h1 { margin-bottom: 0.25rem; }

        .subtitulo {
            color: #52606d;
            margin-bottom: 1.5rem;
        }

        form {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        label { font-weight: bold; }

        input[type="number"] {
            width: 100px;
            padding: 0.5rem;
            border: 1px solid #cbd2d9;
            border-radius: 4px;
        }

        button {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            background: #2563eb;
            color: #ffffff;
            cursor: pointer;
        }

        button:hover { background: #1d4ed8; }

        .error {
            color: #b91c1c;
            background: #fef2f2;
            border: 1px solid #fecaca;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .info {
            margin-bottom: 1.5rem;
            padding: 0.75rem 1rem;
            background: #eff6ff;
            border-radius: 6px;
        }

        ol {
            background: #ffffff;
            padding: 1rem 1rem 1rem 2.5rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        li {
            margin: 0.25rem 0;
            font-family: Consolas, monospace;
        }

        .grafico {
            display: flex;
            align-items: flex-end;
            gap: 0.5rem;
            height: 280px;
            margin-top: 1.5rem;
            padding: 1rem;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
        }

        .barra-contenedor {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 36px;
            height: 100%;
            justify-content: flex-end;
        }

        .barra {
            width: 100%;
            min-height: 4px;
            background: linear-gradient(180deg, #3b82f6, #1d4ed8);
            border-radius: 4px 4px 0 0;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            position: relative;
        }

        .barra-valor {
            position: absolute;
            top: -1.4rem;
            font-size: 0.75rem;
            font-weight: bold;
            color: #334e68;
            white-space: nowrap;
        }

        .barra-etiqueta {
            margin-top: 0.35rem;
            font-size: 0.75rem;
            color: #627d98;
        }
    </style>
</head>
<body>
    <h1>Secuencia de Fibonacci</h1>
    <p class="subtitulo">Prueba Técnica GERET — PHP</p>

    <form method="post" action="">
        <label for="n">Cantidad de términos (N):</label>
        <input
            type="number"
            id="n"
            name="n"
            min="<?php echo N_MIN; ?>"
            max="<?php echo N_MAX; ?>"
            value="<?php echo htmlspecialchars((string) $nActual, ENT_QUOTES, 'UTF-8'); ?>"
            required
        >
        <button type="submit">Calcular</button>
    </form>

    <?php if ($error !== null): ?>
        <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <p class="info">
        Mostrando <strong><?php echo count($secuencia); ?></strong> términos
        (N = <?php echo htmlspecialchars((string) $nActual, ENT_QUOTES, 'UTF-8'); ?>).
        <?php if ($tieneSesion): ?>
            Valor persistido en sesión.
        <?php endif; ?>
    </p>

    <h2>Secuencia</h2>
    <ol>
        <?php foreach ($secuencia as $valor): ?>
            <li><?php echo htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8'); ?></li>
        <?php endforeach; ?>
    </ol>

    <h2>Gráfico de barras</h2>
    <?php echo renderizar_barras($secuencia); ?>
</body>
</html>
