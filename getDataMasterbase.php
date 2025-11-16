<?php

/**
 * Genera un array de aperturas únicas de la API de MasterBase.
 *
 */
function generarReporte($cliente, $usuario, $Token, $mailId, $ElemXPag, $MaxPag) {

    // Autenticación Básica
    $credencialesBase64 = base64_encode("$usuario:$Token");
    $headers = [
        'Authorization: Basic ' . $credencialesBase64,
        'Content-Type: application/xml'
    ];

    // Opciones para la llamada cURL
    $options = [
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,  // Devolver la transferencia como string
        CURLOPT_FOLLOWLOCATION => true,  // Seguir cualquier redirección
        CURLOPT_SSL_VERIFYPEER => false, // No verificar el certificado SSL (para entornos de prueba, en prod se recomienda TRUE)
    ];

    // Cabeceras de la respuesta (equivalente a los encabezados de la hoja de cálculo)
    $datosAperturasUnicas = [];
    // Usado como Set para verificar unicidad
    $emailsUnicos         = [];

    // Paginación y Llamada a la API
    for ($page=1; $page < $MaxPag; $page++) {
        // Llamada a la API
        $url         = "https://api2023.masterbase.com/massivemail/v1/".$cliente."/getEvent/".$mailId."/open?pageNumber=".$page."&pageSize=".$ElemXPag;
        $ch          = curl_init($url);
        curl_setopt_array($ch, $options);
        $xmlResponse = curl_exec($ch);
        $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Verificar la respuesta HTTP (ej: 200 OK)
        if ($httpCode !== 200 || $xmlResponse === false) {
            // Manejo de error o fin de la paginación si la respuesta no es 200
            error_log("Error al llamar a la API para MailId $mailId en la página $page. HTTP Code: $httpCode");
            break;
        }

        // Procesamiento json
        try {
            // Suponemos que la respuesta es un json válido
            $doc = json_decode($xmlResponse, true);
        } catch (Exception $e) {
            error_log("Error al parsear el json para MailId $mailId: " . $e->getMessage());
            break;
        }

        // Navegación en el XML: Root -> Response -> Records
        $recordsNode = $doc['Response']['Records'] ?? null;

        if (!$recordsNode || !isset($recordsNode['Record'])) {
            // No hay nodo Records o no hay registros (fin de la paginación)
            break;
        }

        $records = $recordsNode['Record'];

        // Filtrado de Aperturas Únicas
        foreach ($records as $record) {
            // El atributo se accede directamente con la sintaxis de array en SimpleXML
            $email = (string) $record['Email'];
            if (!isset($emailsUnicos[$email])) {
                // Agregar a la lista de emails únicos
                $emailsUnicos[$email] = true;
                // Agregar la fila a los datos de salida
                $datosAperturasUnicas[] = [
                    $mailId,
                    (string) $record['Email'],
                    (string) $record['EventDate'],
                    (string) $record['OperatingSystem'],
                    (string) $record['Platform'],
                    (string) $record['Country'],
                    (string) $mailId.' - '.$record['Email']
                ];
            }
        }
    }

    // Devolver los datos procesados
    return $datosAperturasUnicas;
}

// --------------------------------------------------------------------------------

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

?>

<table>
    <thead>
        <tr>
            <th>MailId</th>
            <th>Email</th>
            <th>EventDate</th>
            <th>Sistema Operativo</th>
            <th>Plataforma</th>
            <th>País</th>
            <th>MailId + Nombre</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($resultados)&&!empty($resultados)){
            //Recorro
            foreach($resultados as $data){
                echo '
                <tr>
                    <td>'.$data[0].'</td>
                    <td>'.$data[1].'</td>
                    <td>'.$data[2].'</td>
                    <td>'.$data[3].'</td>
                    <td>'.$data[4].'</td>
                    <td>'.$data[5].'</td>
                    <td>'.$data[6].'</td>
                </tr>
                ';
                }
            } ?>
    </tbody>
</table>
