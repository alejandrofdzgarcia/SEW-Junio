<?php
session_start();
$usuarioLogueado = isset($_SESSION['usuario_id']);
$nombreUsuario = $usuarioLogueado ? $_SESSION['usuario_nombre'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="multimedia/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Muros del Nalón</title>
    <meta name="author" content="Alejandro Fernández García"/>
    <meta name="description" content="Reservas - Muros del Nalón"/>
    <meta name="keywords" content="reservas, central"/>
    <link rel="stylesheet" type="text/css" href="estilo/estilo.css">
    <link rel="stylesheet" type="text/css" href="estilo/layout.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header>
        <h1><a href="index.html" title="Inicio Muros del Nalón">Muros del Nalón</a></h1>
        <nav>
            <a href="index.html" title="Inicio Muros del Nalón">Página principal</a>
            <a href="gastronomia.html" title="Gastronomía Muros del Nalón">Gastronomía</a>
            <a href="rutas.html" title="Rutas Muros del Nalón">Rutas</a>
            <a href="meteorologia.html" title="Meteorología Muros del Nalón">Meteorología</a>
            <a href="juego.html" title="Juego Muros del Nalón">Juego</a>
            <a href="reservas.php" title="Reservas Muros del Nalón" class="active">Reservas</a>
            <a href="ayuda.html" title="Ayuda Muros del Nalón">Ayuda</a>
        </nav>
    </header>
    
    <p>Estás en: <a href="index.html">Inicio</a> >> Reservas</p>
    
    <main>
        <h1>Central de Reservas Turísticas</h1>
        
        <!-- Bienvenida y estado de sesión -->
        <section>
            <?php if($usuarioLogueado): ?>
                <p>Bienvenido/a, <strong><?php echo htmlspecialchars($nombreUsuario); ?></strong></p>
                <a href="php/logout.php">Cerrar sesión</a>
            <?php else: ?>
                <p>Inicia sesión para gestionar tus reservas</p>
                <nav>
                    <a href="php/login.php">Iniciar sesión</a>
                    <a href="php/register.php">Registrarse</a>
                </nav>
            <?php endif; ?>
        </section>
        
        <!-- Menú principal de la central de reservas -->
        <section>
            <h2>Gestión de Recursos Turísticos</h2>
            
            <nav>
                <!-- Catálogo de recursos turísticos - accesible a todos -->
                <article>
                    <h3>Catálogo de Recursos</h3>
                    <p>Explora nuestros recursos turísticos disponibles.</p>
                    <a href="php/recursos_turisticos.php">Ver recursos</a>
                </article>
                
                <?php if($usuarioLogueado): ?>
                    <!-- Opciones solo para usuarios registrados -->
                    <article>
                        <h3>Hacer una Reserva</h3>
                        <p>Reserva el recurso turístico que más te interese.</p>
                        <a href="php/realizar_reserva.php">Reservar ahora</a>
                    </article>
                    
                    <article>
                        <h3>Mis Reservas</h3>
                        <p>Consulta y gestiona tus reservas actuales.</p>
                        <a href="php/mis_reservas.php">Ver mis reservas</a>
                    </article>
                    
                    <article>
                        <h3>Cancelar Reserva</h3>
                        <p>Anula reservas que ya no necesites.</p>
                        <a href="php/cancelar_reserva.php">Cancelar reserva</a>
                    </article>
                <?php else: ?>
                    <!-- Mensaje para usuarios no registrados -->
                    <article>
                        <p>Para realizar reservas, consultar o cancelarlas, debes iniciar sesión.</p>
                        <p>Si no tienes una cuenta, regístrate para acceder a todos los servicios.</p>
                    </article>
                <?php endif; ?>
            </nav>
        </section>
        
        <!-- Información adicional sobre las reservas -->
        <section>
            <h2>Información sobre Reservas</h2>
            <article>
                <h3>¿Cómo funciona?</h3>
                <ol>
                    <li>Regístrate en nuestra plataforma o inicia sesión si ya tienes cuenta.</li>
                    <li>Explora el catálogo de recursos turísticos disponibles.</li>
                    <li>Selecciona el recurso que deseas reservar.</li>
                    <li>Revisa el presupuesto generado automáticamente.</li>
                    <li>Confirma tu reserva.</li>
                </ol>
            </article>
            
            <article>
                <h3>Política de cancelación</h3>
                <ul>
                    <li>Las cancelaciones con más de 48 horas de antelación tendrán un reembolso del 100%.</li>
                    <li>Las cancelaciones entre 24 y 48 horas antes tendrán un reembolso del 50%.</li>
                    <li>Las cancelaciones con menos de 24 horas no tienen reembolso.</li>
                </ul>
            </article>
        </section>
    </main>

    <footer>
        <p>2025 Turismo de Muros del Nalón</p>
        <p><a href="https://www.uniovi.es">Universidad de Oviedo</a> 
            - <a href="https://www.uniovi.es/estudia/grados/ingenieria/informaticasoftware/-/fof/asignatura/GIISOF01-3-002">Software y Estándares para la Web</a></p>
        <p><a href="https://github.com/alejandrofdzgarcia">Diseñado por Alejandro Fernández García</a></p>
    </footer>
</body>
</html>