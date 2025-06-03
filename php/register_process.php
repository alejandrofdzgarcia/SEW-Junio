<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
    
    // todos los campos deben de estar completos
    if (empty($nombre) || empty($email) || empty($password) || empty($password_confirm)) {
        $_SESSION['error'] = 'Todos los campos son obligatorios';
        $_SESSION['form_data'] = [
            'nombre' => $nombre,
            'email' => $email
        ];
        header('Location: register.php');
        exit;
    }
    
    // comprobar igualdad de contraseñas
    if ($password !== $password_confirm) {
        $_SESSION['error'] = 'Las contraseñas no coinciden';
        $_SESSION['form_data'] = [
            'nombre' => $nombre,
            'email' => $email
        ];
        header('Location: register.php');
        exit;
    }
    
    // comprobar formato del email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'El formato del email no es válido';
        $_SESSION['form_data'] = [
            'nombre' => $nombre
        ];
        header('Location: register.php');
        exit;
    }
    
    // conectar a la base de datos
    try {
        $dsn = 'mysql:host=localhost;dbname=muros_nalon;charset=utf8mb4';
        $usuario_db = 'DBUSER2025';
        $password_db = 'DBPWD2025';
        $opciones = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
        
        $pdo = new PDO($dsn, $usuario_db, $password_db, $opciones);
        
        // comprobar si el email ya está registrado
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = 'El email ya está registrado';
            $_SESSION['form_data'] = [
                'nombre' => $nombre
            ];
            header('Location: register.php');
            exit;
        }
        
        
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $email, $password_hash]);
        
        $usuario_id = $pdo->lastInsertId();
        
        $_SESSION['usuario_id'] = $usuario_id;
        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario_email'] = $email;
        
        header('Location: ../reservas.php');
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error al registrar el usuario: ' . $e->getMessage();
        $_SESSION['form_data'] = [
            'nombre' => $nombre,
            'email' => $email
        ];
        header('Location: register.php');
        exit;
    }
    
} else {
    header('Location: register.php');
    exit;
}
