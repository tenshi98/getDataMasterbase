# getDataMasterbase
Genera un array de aperturas únicas de los correos utilizando la API de [MasterBase](https://masterbase.com/es/), una plataforma especializada en el marketing.

---

## Uso

1. Copia y pega la funcion donde puedas llamarla desde cualquier parte del software o dentro del mismo archivo a ejecutar.
2. Completa los datos de credenciales y configuración.
3. Utiliza la variable **$resultados** para procesar la respuesta de la API.


```php
// Credenciales y Configuración
$cliente  = 'cliente';  //Cliente (Nombre del cliente)
$usuario  = 'usuario';  //usuario (Usuario de acceso para la autenticacion)
$Token    = 'Token';    //token (token de acceso para la autenticacion)
$mailId   = 5555;       //ID de la campaña a consultar
$ElemXPag = 10000;      //Elementos X Pagina
$MaxPag   = 15;         //Maximo de Paginas, tener en cuenta la cantidad maxima que se preveen es este dato x Elementos X Pagina

// Ejemplo de Uso (PHP)
$resultados = generarReporte($cliente, $usuario, $Token, $mailId, $ElemXPag, $MaxPag);

// Aquí podrías procesar $resultados para escribir en un archivo CSV, una base de datos, o imprimirlos.
//print_r($resultados);
```
