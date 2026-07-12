# certs/

Aquí va el **certificado CA de Aiven** para la conexión TLS con MySQL.

## Cómo obtenerlo

1. En Aiven, entra a tu servicio MySQL → **Connection information**.
2. Descarga **CA Certificate**.
3. Guárdalo en esta carpeta **exactamente** como:

   ```
   certs/ca.pem
   ```

Este archivo **SÍ** se commitea al repo (el CA es público, no contiene tu contraseña),
para que quede dentro de la imagen Docker que se despliega en Render.

## Variable relacionada (se configura en Render → Environment)

```
MYSQL_ATTR_SSL_CA=/var/www/html/certs/ca.pem
```

`config/database.php` (conexión `mysql`) ya lee esa variable vía
`PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA')`.
