"""
Script principal — Consumo de API Open-Meteo y persistencia en MySQL.
Prueba Técnica GERET — Python
"""

import os
import sys
from datetime import datetime

import pymysql
import requests
from dotenv import load_dotenv

load_dotenv()

OPEN_METEO_URL = "https://api.open-meteo.com/v1/forecast"
REQUEST_TIMEOUT = 10


def get_db_config() -> dict:
    """
    Lee la configuración de MySQL desde variables de entorno.

    Returns:
        dict: Diccionario con host, port, database, user y password.
    """
    return {
        "host": os.getenv("DB_HOST", "localhost"),
        "port": int(os.getenv("DB_PORT", "3306")),
        "database": os.getenv("DB_NAME", "weather_db"),
        "user": os.getenv("DB_USER"),
        "password": os.getenv("DB_PASSWORD"),
    }


def get_weather_config() -> dict:
    """
    Lee la configuración de la ciudad desde variables de entorno.

    Returns:
        dict: Diccionario con city_name, latitude y longitude.
    """
    return {
        "city_name": os.getenv("CITY_NAME", "Santiago"),
        "latitude": float(os.getenv("LATITUDE", "-33.45")),
        "longitude": float(os.getenv("LONGITUDE", "-70.67")),
    }


def fetch_weather(latitude: float, longitude: float) -> dict:
    """
    Consulta la API Open-Meteo y obtiene datos meteorológicos actuales.

    Args:
        latitude: Latitud de la ciudad a consultar.
        longitude: Longitud de la ciudad a consultar.

    Returns:
        dict: Datos actuales con temperature, windspeed, humidity y weathercode.

    Raises:
        requests.RequestException: Si hay error de red, timeout o respuesta HTTP inválida.
        ValueError: Si la respuesta JSON no contiene los campos esperados.
    """
    params = {
        "latitude": latitude,
        "longitude": longitude,
        "current": "temperature_2m,windspeed_10m,weathercode,relative_humidity_2m",
    }

    try:
        response = requests.get(
            OPEN_METEO_URL,
            params=params,
            timeout=REQUEST_TIMEOUT,
        )
        response.raise_for_status()
    except requests.Timeout as exc:
        raise requests.RequestException("La petición a Open-Meteo excedió el tiempo de espera.") from exc
    except requests.ConnectionError as exc:
        raise requests.RequestException("No se pudo conectar con la API Open-Meteo.") from exc
    except requests.HTTPError as exc:
        raise requests.RequestException(
            f"Error HTTP {response.status_code} al consultar Open-Meteo."
        ) from exc

    data = response.json()
    current = data.get("current")

    if not current:
        raise ValueError("La respuesta de la API no incluye datos actuales (current).")

    required_fields = (
        "temperature_2m",
        "windspeed_10m",
        "weathercode",
        "relative_humidity_2m",
    )

    for field in required_fields:
        if field not in current:
            raise ValueError(f"Campo requerido ausente en la respuesta: {field}")

    return {
        "temperature": current["temperature_2m"],
        "windspeed": current["windspeed_10m"],
        "humidity": current["relative_humidity_2m"],
        "weathercode": current["weathercode"],
    }


def create_table_if_not_exists(connection) -> None:
    """
    Crea la tabla weather_records si no existe.

    Args:
        connection: Conexión activa a MySQL.
    """
    create_sql = """
        CREATE TABLE IF NOT EXISTS weather_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            city_name VARCHAR(100) NOT NULL,
            temperature DECIMAL(5, 2) NOT NULL,
            windspeed DECIMAL(5, 2) NOT NULL,
            humidity DECIMAL(5, 2) NOT NULL,
            weathercode INT NOT NULL,
            fetched_at DATETIME NOT NULL
        )
    """

    with connection.cursor() as cursor:
        cursor.execute(create_sql)


def save_to_db(weather_data: dict, city_name: str, db_config: dict) -> None:
    """
    Persiste un registro meteorológico en la base de datos MySQL.

    Args:
        weather_data: Diccionario con temperature, windspeed, humidity y weathercode.
        city_name: Nombre de la ciudad consultada.
        db_config: Configuración de conexión a MySQL.

    Raises:
        pymysql.MySQLError: Si falla la conexión o la inserción.
    """
    connection = pymysql.connect(
        host=db_config["host"],
        port=db_config["port"],
        user=db_config["user"],
        password=db_config["password"],
        database=db_config["database"],
        charset="utf8mb4",
        cursorclass=pymysql.cursors.DictCursor,
    )

    try:
        create_table_if_not_exists(connection)

        insert_sql = """
            INSERT INTO weather_records
                (city_name, temperature, windspeed, humidity, weathercode, fetched_at)
            VALUES (%s, %s, %s, %s, %s, %s)
        """

        fetched_at = datetime.now()

        with connection.cursor() as cursor:
            cursor.execute(
                insert_sql,
                (
                    city_name,
                    weather_data["temperature"],
                    weather_data["windspeed"],
                    weather_data["humidity"],
                    weather_data["weathercode"],
                    fetched_at,
                ),
            )

        connection.commit()
        print(f"Registro guardado para {city_name} ({fetched_at.strftime('%Y-%m-%d %H:%M:%S')}).")
    finally:
        connection.close()


def list_recent_records(db_config: dict, limit: int = 5) -> None:
    """
    Imprime por consola los últimos registros almacenados en weather_records.

    Args:
        db_config: Configuración de conexión a MySQL.
        limit: Cantidad de registros a mostrar (por defecto 5).
    """
    connection = pymysql.connect(
        host=db_config["host"],
        port=db_config["port"],
        user=db_config["user"],
        password=db_config["password"],
        database=db_config["database"],
        charset="utf8mb4",
        cursorclass=pymysql.cursors.DictCursor,
    )

    try:
        create_table_if_not_exists(connection)

        select_sql = """
            SELECT id, city_name, temperature, windspeed, humidity, weathercode, fetched_at
            FROM weather_records
            ORDER BY fetched_at DESC
            LIMIT %s
        """

        with connection.cursor() as cursor:
            cursor.execute(select_sql, (limit,))
            records = cursor.fetchall()

        if not records:
            print("No hay registros almacenados.")
            return

        print(f"\nÚltimos {len(records)} registros:")
        print("-" * 80)

        for record in records:
            print(
                f"ID: {record['id']} | {record['city_name']} | "
                f"Temp: {record['temperature']}°C | Viento: {record['windspeed']} km/h | "
                f"Humedad: {record['humidity']}% | Código: {record['weathercode']} | "
                f"Fecha: {record['fetched_at']}"
            )

        print("-" * 80)
    finally:
        connection.close()


def validate_config(db_config: dict) -> None:
    """
    Verifica que las credenciales obligatorias estén definidas.

    Args:
        db_config: Configuración de conexión a MySQL.

    Raises:
        ValueError: Si faltan variables de entorno requeridas.
    """
    if not db_config["user"]:
        raise ValueError("DB_USER no está definido en el archivo .env")
    if db_config["password"] is None:
        raise ValueError("DB_PASSWORD no está definido en el archivo .env")


def main() -> int:
    """
    Punto de entrada del script.

    Returns:
        int: Código de salida (0 = éxito, 1 = error).
    """
    try:
        weather_config = get_weather_config()
        db_config = get_db_config()
        validate_config(db_config)

        print(
            f"Consultando clima para {weather_config['city_name']} "
            f"({weather_config['latitude']}, {weather_config['longitude']})..."
        )

        weather_data = fetch_weather(
            weather_config["latitude"],
            weather_config["longitude"],
        )

        print(
            f"Datos obtenidos — Temp: {weather_data['temperature']}°C, "
            f"Viento: {weather_data['windspeed']} km/h, "
            f"Humedad: {weather_data['humidity']}%, "
            f"Código: {weather_data['weathercode']}"
        )

        save_to_db(weather_data, weather_config["city_name"], db_config)
        list_recent_records(db_config)

        return 0

    except (requests.RequestException, ValueError, pymysql.MySQLError) as exc:
        print(f"Error: {exc}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(main())
