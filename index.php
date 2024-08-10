<?php
$carpetaNombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$carpetaRuta = "./descarga/" . $carpetaNombre;

try {
    if (!file_exists($carpetaRuta)) {
        mkdir($carpetaRuta, 0755, true);
        $mensaje = "Carpeta '$carpetaNombre' creada con éxito.";
    } else {
        $mensaje = "La carpeta '$carpetaNombre' ya existe.";
    }

    // Manejo de archivos en el método POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_FILES['archivo']) && is_array($_FILES['archivo']['name'])) {
            $totalArchivos = count($_FILES['archivo']['name']);
            for ($i = 0; $i < $totalArchivos; $i++) {
                $archivoTmpPath = $_FILES['archivo']['tmp_name'][$i];
                $archivoName = $_FILES['archivo']['name'][$i];
                $archivoError = $_FILES['archivo']['error'][$i];

                $archivoName = str_replace(' ', '_', $archivoName);

                if ($archivoError === UPLOAD_ERR_OK) {
                    $destino = $carpetaRuta . '/' . $archivoName;
                    if (move_uploaded_file($archivoTmpPath, $destino)) {
                        $mensaje = "Archivo '$archivoName' subido con éxito.";
                    } else {
                        throw new Exception("Error al subir el archivo '$archivoName'.");
                    }
                } else {
                    throw new Exception("Error al subir el archivo '$archivoName'.");
                }
            }
        } elseif (isset($_FILES['archivo']) && is_string($_FILES['archivo']['name'])) {
            // Manejo de un solo archivo
            $archivoTmpPath = $_FILES['archivo']['tmp_name'];
            $archivoName = $_FILES['archivo']['name'];
            $archivoError = $_FILES['archivo']['error'];

            if ($archivoError === UPLOAD_ERR_OK) {
                $destino = $carpetaRuta . '/' . $archivoName;
                if (move_uploaded_file($archivoTmpPath, $destino)) {
                    $mensaje = "Archivo '$archivoName' subido con éxito.";
                } else {
                    throw new Exception("Error al subir el archivo '$archivoName'.");
                }
            } else {
                throw new Exception("Error al subir el archivo '$archivoName'.");
            }
        }

        // Eliminación de archivos
        if (isset($_POST['eliminarArchivo'])) {
            $archivoAEliminar = $_POST['eliminarArchivo'];
            $archivoRutaAEliminar = $carpetaRuta . '/' . $archivoAEliminar;

            if (file_exists($archivoRutaAEliminar)) {
                if (unlink($archivoRutaAEliminar)) {
                    $mensaje = "Archivo '$archivoAEliminar' eliminado con éxito.";
                } else {
                    throw new Exception("Error al eliminar el archivo '$archivoAEliminar'.");
                }
            } else {
                throw new Exception("El archivo '$archivoAEliminar' no existe.");
            }
        }
    }
} catch (Exception $e) {
    $mensaje = "Error: " . htmlspecialchars($e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compartir archivos</title>
    <script src="parametro.js"></script>
    <link rel="stylesheet" href="estilo.css">
</head>

<body>
    <h1 >Compartir archivos <sup class="beta">BETA</sup></h1>
    <div class="content">
        <h3>Sube tus archivos y comparte este enlace temporal: <span>ibu.pe/?nombre=<?php echo htmlspecialchars($carpetaNombre); ?></span></h3>
        <div class="container">
            <div class="drop-area" id="drop-area" >
                <form action="" id="form" method="POST" enctype="multipart/form-data">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" style="fill:#0730c5;transform: ;msFilter:; class="bi bi-file-earmark-arrow-up" viewBox="0 0 16 16"><path d="M8.5 11.5a.5.5 0 0 1-1 0V7.707L6.354 8.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 7.707z"/><path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/></svg><br>
                    <input type="file" class="file-input" name="archivo[]" id="archivo" onchange="document.getElementById('form').submit()" multiple>
                    <label> Arrastra tus archivos aquí<br>o</label>
                    <p><label class="botn" for="archivo">Abre el explorador</label></p>
                    
                </form>
            </div>

            <div class="container2">
                <div id="file-list" class="pila">
                    <?php
                    $targetDir = $carpetaRuta;
                    $files = scandir($targetDir);
                    $files = array_diff($files, array('.', '..'));

                    if (count($files) > 0) {
                        echo "<h3 style='margin-bottom:10px;'>Archivos Subidos:</h3>";

                        foreach ($files as $file) {
                            echo "<div class='archivos_subidos'>
                            <div class='nombre'><a href='$carpetaRuta/$file' download class='boton-descargar'>$file</a></div>
                            <div class='boton'>
                            <form action='' method='POST' style='display:inline;'>
                                <input type='hidden' name='eliminarArchivo' value='$file'>
                                <button type='submit' class='btn_delete'>
                                    <svg xmlns='http://www.w3.org/2000/svg' class='icon icon-tabler icon-tabler-trash' width='24' height='24' viewBox='0 0 24 24' stroke-width='2' stroke='currentColor' fill='none' stroke-linecap='round' stroke-linejoin='round'>
                                        <path stroke='none' d='M0 0h24v24H0z' fill='none'/>
                                        <path d='M4 7l16 0' />
                                        <path d='M10 11l0 6' />
                                        <path d='M14 11l0 6' />
                                        <path d='M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12' />
                                        <path d='M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3' />
                                    </svg>
                                </button>
                               

                            </form>
                             
                        </div>
                        </div>";
                        }
                    } else {
                        echo "No se han subido archivos.";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <footer>Ze Roberto Cabezudo Flores</footer>
    <script src="parametro.js"></script>
</body>

</html>
