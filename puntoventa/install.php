<?php
// install.php - Script de instalación
//session_start();

if(file_exists('config/database.php')) {
    die('El sistema ya está instalado. Elimina este archivo (install.php) por seguridad.');
}

if($_POST) {
    $host = $_POST['host'];
    $dbname = $_POST['dbname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        // Intentar conexión
        $conn = new PDO("mysql:host=$host", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Crear base de datos si no existe
        $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname");
        $conn->exec("USE $dbname");
        
        // Leer y ejecutar el script SQL
        $sql = file_get_contents('punto_venta.sql');
        $conn->exec($sql);
        
        // Crear archivo de configuración
        $configContent = "<?php
class Database {
    private \$host = \"$host\";
    private \$db_name = \"$dbname\";
    private \$username = \"$username\";
    private \$password = \"$password\";
    public \$conn;

    public function getConnection() {
        \$this->conn = null;
        try {
            \$this->conn = new PDO(\"mysql:host=\" . \$this->host . \";dbname=\" . \$this->db_name, \$this->username, \$this->password);
            \$this->conn->exec(\"set names utf8\");
        } catch(PDOException \$exception) {
            echo \"Error de conexión: \" . \$exception->getMessage();
        }
        return \$this->conn;
    }
}
?>";
        
        file_put_contents('config/database.php', $configContent);
        
        // Eliminar este script de instalación
        unlink(__FILE__);
        
        header("Location: login.php");
        exit;
        
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Sistema Punto de Venta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Instalación del Sistema</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="host" class="form-label">Servidor MySQL</label>
                                <input type="text" class="form-control" id="host" name="host" value="crmregistroventas.online" required>
                            </div>
                            <div class="mb-3">
                                <label for="dbname" class="form-label">Nombre de la Base de Datos</label>
                                <input type="text" class="form-control" id="dbname" name="dbname" value="punto_venta" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuario MySQL</label>
                                <input type="text" class="form-control" id="username" name="username" value="noreplyc_alexsanchez09" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña MySQL</label>
                                <input type="password" class="form-control" id="password" name="password">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Instalar Sistema</button>
                        </form>
                        
                        <div class="alert alert-info mt-3">
                            <small>
                                <strong>Nota:</strong> Este script creará la base de datos y todas las tablas necesarias.
                                Asegúrate de tener permisos para crear bases de datos.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>