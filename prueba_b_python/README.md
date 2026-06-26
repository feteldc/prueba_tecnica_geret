# Prueba B — API de Clima con Python

Script Python que consume la API pública [Open-Meteo](https://open-meteo.com) para obtener datos meteorológicos actuales de una ciudad configurable y los persiste en una base de datos MySQL local.

## Requisitos

- Python 3.9 o superior
- MySQL 5.7+ o MariaDB 10.3+
- Acceso a internet para consultar la API

## Instalación

1. Clona el repositorio y entra a la carpeta del proyecto:

```bash
git clone <url-del-repositorio>
cd prueba_b_python
```

2. Crea y activa un entorno virtual (recomendado):

```bash
python -m venv venv

# Windows
venv\Scripts\activate

# Linux / macOS
source venv/bin/activate
```

3. Instala las dependencias:

```bash
pip install -r requirements.txt
```

4. Crea la base de datos en MySQL:

```sql
CREATE DATABASE weather_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

5. Copia el archivo de configuración y edítalo con tus credenciales:

```bash
copy .env.example .env
```

En Linux/macOS:

```bash
cp .env.example .env
```

## Variables de configuración (.env)

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `CITY_NAME` | Nombre de la ciudad | `Santiago` |
| `LATITUDE` | Latitud de la ciudad | `-33.45` |
| `LONGITUDE` | Longitud de la ciudad | `-70.67` |
| `DB_HOST` | Host de MySQL | `localhost` |
| `DB_PORT` | Puerto de MySQL | `3306` |
| `DB_NAME` | Nombre de la base de datos | `weather_db` |
| `DB_USER` | Usuario de MySQL | `root` |
| `DB_PASSWORD` | Contraseña de MySQL | `tu_contrasena` |

> **Importante:** El archivo `.env` real no debe subirse al repositorio. Usa `.env.example` como plantilla.

## Ejecución

Con el entorno virtual activado y el archivo `.env` configurado:

```bash
python main.py
```

El script:

1. Consulta la API Open-Meteo con las coordenadas configuradas.
2. Crea la tabla `weather_records` si no existe.
3. Inserta un nuevo registro con temperatura, viento, humedad, código meteorológico y fecha/hora.
4. Muestra por consola los últimos 5 registros almacenados.

## Estructura de la tabla

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT AUTO_INCREMENT | Identificador único |
| `city_name` | VARCHAR(100) | Nombre de la ciudad |
| `temperature` | DECIMAL(5,2) | Temperatura en °C |
| `windspeed` | DECIMAL(5,2) | Velocidad del viento en km/h |
| `humidity` | DECIMAL(5,2) | Humedad relativa en % |
| `weathercode` | INT | Código WMO del clima |
| `fetched_at` | DATETIME | Fecha y hora de la consulta |

## Funciones principales

| Función | Descripción |
|---------|-------------|
| `fetch_weather(latitude, longitude)` | Consulta Open-Meteo y devuelve datos actuales |
| `save_to_db(weather_data, city_name, db_config)` | Inserta un registro en MySQL |
| `list_recent_records(db_config, limit=5)` | Imprime los últimos registros por consola |

## Manejo de errores

- Errores de red, timeout o HTTP distinto de 200 al consultar la API.
- Respuesta JSON incompleta o campos faltantes.
- Fallos de conexión o inserción en MySQL.
- Variables de entorno obligatorias no definidas.

## Verificación en MySQL

Después de ejecutar `python main.py`, comprueba los datos guardados:

```sql
USE weather_db;
SELECT * FROM weather_records ORDER BY fetched_at DESC LIMIT 5;
```

Cada ejecución del script debe agregar un nuevo registro a la tabla.

## Ejemplo de salida

```
Consultando clima para Santiago (-33.45, -70.67)...
Datos obtenidos — Temp: 18.5°C, Viento: 12.3 km/h, Humedad: 65.0%, Código: 2
Registro guardado para Santiago (2026-06-25 14:30:00).

Últimos 1 registros:
--------------------------------------------------------------------------------
ID: 1 | Santiago | Temp: 18.50°C | Viento: 12.30 km/h | Humedad: 65.00% | Código: 2 | Fecha: 2026-06-25 14:30:00
--------------------------------------------------------------------------------
```
