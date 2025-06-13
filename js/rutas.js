class Rutas {
    constructor() {
        this.rutas = [];
        this.mapas = [];
    }

    cargarDesdeArchivo(archivo) {
        const reader = new FileReader();
        reader.onload = (e) => {
            try {
                const xmlDoc = $.parseXML(e.target.result);
                const $xml = $(xmlDoc);
                this.procesarXML($xml);
                this.mostrarRutas();
            } catch (error) {
                this.mostrarError("Error al procesar el archivo XML: " + error.message);
            }
        };
        reader.onerror = () => {
            this.mostrarError("Error al leer el archivo");
        };
        reader.readAsText(archivo);
    }

    procesarXML($xml) {
        this.rutas = [];
        const $rutasXML = $xml.find("ruta");
        
        $rutasXML.each((_, ruta) => {
            const $ruta = $(ruta);
            const nuevaRuta = {
                nombre: $ruta.find("nombre").text(),
                descripcion: $ruta.find("descripcion").text(),
                distancia: $ruta.find("distancia").text(),
                duracion: $ruta.find("duracion").text(),
                dificultad: $ruta.find("dificultad").text(),
                puntoInicio: $ruta.find("puntoInicio").text(),
                puntoFin: $ruta.find("puntoFin").text(),
                imagenes: []
            };
            
            $ruta.find("imagen").each((_, img) => {
                nuevaRuta.imagenes.push($(img).text());
            });
            
            this.rutas.push(nuevaRuta);
        });
    }

    mostrarRutas() {
        const $container = $("#rutasContainer");
        $container.empty();
        
        if (this.rutas.length === 0) {
            $container.html("<p>No se encontraron rutas en el archivo XML.</p>");
            return;
        }
        
        this.rutas.forEach(ruta => {
            const rutaHTML = `
                <article>
                    <h3>${ruta.nombre}</h3>
                    <p>${ruta.descripcion}</p>
                    <div>
                        <p><strong>Distancia:</strong> ${ruta.distancia}</p>
                        <p><strong>Duración estimada:</strong> ${ruta.duracion}</p>
                        <p><strong>Dificultad:</strong> ${ruta.dificultad}</p>
                        <p><strong>Punto de inicio:</strong> ${ruta.puntoInicio}</p>
                        <p><strong>Punto final:</strong> ${ruta.puntoFin}</p>
                    </div>
                    ${this.generarGaleriaImagenes(ruta.imagenes)}
                </article>
            `;
            $container.append(rutaHTML);
        });
    }
    
    generarGaleriaImagenes(imagenes) {
        if (imagenes.length === 0) return '';
        
        let galeriaHTML = '<div>';
        imagenes.forEach(img => {
            galeriaHTML += `<img src="${img}" alt="Imagen de la ruta" />`;
        });
        galeriaHTML += '</div>';
        
        return galeriaHTML;
    }
    
    mostrarError(mensaje) {
        $("#rutasContainer").html(`<p>${mensaje}</p>`);
    }
}

// Wrap initialization in DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize FilterContent here
    const rutasApp = new Rutas();
    
    $("#processFile").on("click", function() {
        const fileInput = document.getElementById("fileInput");
        if (fileInput.files.length === 0) {
            rutasApp.mostrarError("Por favor, selecciona un archivo XML");
            return;
        }
        
        const archivo = fileInput.files[0];
        if (archivo.type !== "text/xml" && !archivo.name.endsWith('.xml')) {
            rutasApp.mostrarError("El archivo debe ser de tipo XML");
            return;
        }
        
        rutasApp.cargarDesdeArchivo(archivo);
    });
    
    // Referencia a los elementos
    const fileInput = $("input[name='fileInput']");
    const processButton = $("button[name='processFile']");
    const rutasContainer = $("section[name='rutasContainer']");
    
    // Arrays para almacenar datos
    let rutas = [];
    let mapas = [];
    
    // Función para normalizar nombres de archivo
    function normalizarNombre(nombre) {
        return nombre
            .toLowerCase()
            .replace(/\s+/g, '_')
            .replace(/[áàäâ]/g, 'a')
            .replace(/[éèëê]/g, 'e')
            .replace(/[íìïî]/g, 'i')
            .replace(/[óòöô]/g, 'o')
            .replace(/[úùüû]/g, 'u')
            .replace(/ñ/g, 'n')
            .replace(/[^\w\-]/g, '_');
    }
    
    // Función para mostrar errores
    function mostrarError(mensaje) {
        rutasContainer.html(`<p>${mensaje}</p>`);
    }
    
    // Función para generar HTML de fotografías
    function generarGaleriaFotos(fotografias) {
        if (!fotografias || fotografias.length === 0) return '';
        
        let galeriaHTML = '<section name="galeria-fotos">';
        fotografias.forEach(foto => {
            galeriaHTML += `<img src="${foto}" alt="Fotografía de la ruta" />`;
        });
        galeriaHTML += '</section>';
        
        return galeriaHTML;
    }
    
    // Función para formatear coordenadas
    function formatearCoordenadas(coords) {
        if (!coords) return 'No disponibles';
        return `Latitud: ${coords.latitud || 'N/A'}, Longitud: ${coords.longitud || 'N/A'}, Altitud: ${coords.altitud || 'N/A'} m`;
    }
    
    // Función para mostrar los hitos de una ruta
    function mostrarHitos(hitos) {
        if (!hitos || hitos.length === 0) return '<p>No hay hitos registrados para esta ruta.</p>';
        
        let hitosHTML = '<h4>Puntos de interés en la ruta:</h4>';
        
        hitos.forEach((hito, index) => {
            hitosHTML += `
                <article name="hito">
                    <h5>${index + 1}. ${hito.nombre}</h5>
                    <p>${hito.descripcion}</p>
                    <section name="hito-detalles">
                        <p><strong>Coordenadas:</strong> ${formatearCoordenadas(hito.coordenadas)}</p>
                        <p><strong>Distancia desde el inicio:</strong> ${hito.distancia} ${hito.unidad || ''}</p>
                    </section>
                    ${generarGaleriaFotos(hito.fotografias)}
                </article>
            `;
        });
        
        return hitosHTML;
    }
    
    // Función para mostrar referencias
    function mostrarReferencias(referencias) {
        if (!referencias || referencias.length === 0) return '';
        
        let refHTML = '<section name="referencias"><h4>Referencias:</h4><ul>';
        referencias.forEach(ref => {
            refHTML += `<li><a href="${ref}" target="_blank">${ref}</a></li>`;
        });
        refHTML += '</ul></section>';
        
        return refHTML;
    }
    
    // Función para cargar y mostrar SVG de altimetría
    function cargarAltimetria(nombreRuta) {
        const nombreArchivo = normalizarNombre(nombreRuta);
        
        return `<div>
            <h4>Altimetría de la Ruta</h4>
            <object data="xml/altimetria_${nombreArchivo}.svg" type="image/svg+xml"">
                Tu navegador no soporta SVG
            </object>
        </div>`;
    }
    
    // Función para cargar y mostrar mapa KML con OpenLayers
    function cargarMapa(nombreRuta, index) {
        const mapId = `mapa-${index}`;
        
        return `<div>
            <h4>Mapa de la Ruta</h4>
            <div id="${mapId}"></div>
        </div>`;
    }
    
    // Función para inicializar el mapa con OpenLayers
    function inicializarMapa(id, kmlUrl) {
        const container = document.getElementById(id);
        if (!container) {
            console.error(`Map container with id ${id} not found`);
            return null;
        }
        
        // Force container to have dimensions
        if (container.offsetWidth === 0 || container.offsetHeight === 0) {
            container.style.width = '100%';
            container.style.height = '400px';
        }
        
        const map = new ol.Map({
            target: id,
            layers: [
                new ol.layer.Tile({
                    source: new ol.source.OSM()
                })
            ],
            view: new ol.View({
                center: ol.proj.fromLonLat([-6.1, 43.55]), // Coordenadas aproximadas de Muros del Nalón
                zoom: 13
            })
        });
        
        const vectorLayer = new ol.layer.Vector({
            source: new ol.source.Vector({
                url: kmlUrl,
                format: new ol.format.KML({
                    extractStyles: true
                })
            })
        });
        
        map.addLayer(vectorLayer);
        
        // Force map to update its size
        setTimeout(() => {
            map.updateSize();
        }, 200);
        
        // Ajustar vista cuando se carga el KML
        vectorLayer.getSource().on('addfeature', function() {
            const extent = vectorLayer.getSource().getExtent();
            if (!ol.extent.isEmpty(extent)) {
                map.getView().fit(extent, {
                    padding: [50, 50, 50, 50],
                    duration: 1000
                });
            }
        });
        
        return map;
    }
    
    // Función para mostrar rutas
    function mostrarRutas() {
        rutasContainer.empty();
        
        if (rutas.length === 0) {
            rutasContainer.html("<p>No se encontraron rutas en el archivo XML.</p>");
            return;
        }
        
        // Limpiar mapas anteriores
        mapas.forEach(mapa => {
            if (mapa) {
                mapa.setTarget(null);
            }
        });
        mapas = [];
        
        // Añadir encabezado con el número de rutas encontradas
        rutasContainer.append(`<h2>Se han encontrado ${rutas.length} rutas</h2>`);
        
        // Para cada ruta, crear un artículo
        rutas.forEach((ruta, index) => {
            const rutaHTML = `
                <article name="ruta">
                    <h3>${ruta.nombre}</h3>
                    <section name="info-basica">
                        <p>${ruta.descripcion}</p>
                        <p><strong>Tipo:</strong> ${ruta.tipo || 'No especificado'}</p>
                        <p><strong>Transporte:</strong> ${ruta.transporte || 'No especificado'}</p>
                        <p><strong>Duración estimada:</strong> ${ruta.duracion || 'No especificada'}</p>
                        <p><strong>Fecha de inicio:</strong> ${ruta.fechaInicio || 'Flexible'}</p>
                        <p><strong>Hora de inicio:</strong> ${ruta.horaInicio || 'Flexible'}</p>
                        <p><strong>Agencia:</strong> ${ruta.agencia || 'No especificada'}</p>
                        <p><strong>Personas adecuadas:</strong> ${ruta.personasAdecuadas || 'No especificado'}</p>
                        <p><strong>Recomendación:</strong> ${ruta.recomendacion ? ruta.recomendacion + '/10' : 'No disponible'}</p>
                    </section>
                    
                    <section name="punto-inicio">
                        <h4>Punto de inicio:</h4>
                        <p><strong>Lugar:</strong> ${ruta.puntoInicio?.lugar || 'No especificado'}</p>
                        <p><strong>Dirección:</strong> ${ruta.puntoInicio?.direccion || 'No especificada'}</p>
                        <p><strong>Coordenadas:</strong> ${formatearCoordenadas(ruta.puntoInicio?.coordenadas)}</p>
                    </section>
                    
                    <section name="visualizaciones">
                        ${cargarAltimetria(ruta.nombre)}
                        ${cargarMapa(ruta.nombre, index)}
                    </section>
                    
                    <section name="hitos-ruta">
                        ${mostrarHitos(ruta.hitos)}
                    </section>
                    
                    ${mostrarReferencias(ruta.referencias)}
                </article>
                <hr/>
            `;
            rutasContainer.append(rutaHTML);
            
            // Inicializar el mapa después de que el DOM esté listo
            setTimeout(() => {
                const nombreArchivo = normalizarNombre(ruta.nombre);
                const kmlUrl = `xml/${nombreArchivo}.kml`;
                const mapElement = document.getElementById(`mapa-${index}`);
                
                if (mapElement) {
                    // Make sure container has dimensions before initializing
                    if (mapElement.offsetWidth === 0 || mapElement.offsetHeight === 0) {
                        mapElement.style.width = '100%';
                        mapElement.style.height = '400px';
                    }
                    
                    const mapa = inicializarMapa(`mapa-${index}`, kmlUrl);
                    mapas.push(mapa);
                }
            }, 300); // Longer timeout to ensure DOM is ready
        });
        
        // Añadir información sobre el XML
        const xmlInfo = `
            <section name="xml-info">
                <h3>Información del archivo XML</h3>
                <p>Nombre del archivo: ${fileInput[0].files[0].name}</p>
                <p>Tamaño: ${(fileInput[0].files[0].size / 1024).toFixed(2)} KB</p>
                <p>Fecha de carga: ${new Date().toLocaleString()}</p>
            </section>
        `;
        rutasContainer.append(xmlInfo);
    }
    
    // Función para procesar el XML según el esquema proporcionado
    function procesarXML($xml) {
        rutas = [];
        const $rutasXML = $xml.find("ruta");
        
        $rutasXML.each((_, ruta) => {
            const $ruta = $(ruta);
            
            // Obtener los datos del punto de inicio
            const $puntoInicio = $ruta.find("puntoInicio");
            const puntoInicio = {
                lugar: $puntoInicio.find("lugar").text(),
                direccion: $puntoInicio.find("direccion").text(),
                coordenadas: {
                    longitud: $puntoInicio.find("coordenadas > longitud").text(),
                    latitud: $puntoInicio.find("coordenadas > latitud").text(),
                    altitud: $puntoInicio.find("coordenadas > altitud").text()
                }
            };
            
            // Procesar hitos
            const hitos = [];
            $ruta.find("hitos > hito").each((_, hitoElem) => {
                const $hito = $(hitoElem);
                const fotografias = [];
                
                $hito.find("fotografias > fotografia").each((_, fotoElem) => {
                    fotografias.push($(fotoElem).text());
                });
                
                const distanciaElem = $hito.find("distancia");
                
                hitos.push({
                    nombre: $hito.find("nombre").text(),
                    descripcion: $hito.find("descripcion").text(),
                    coordenadas: {
                        longitud: $hito.find("coordenadas > longitud").text(),
                        latitud: $hito.find("coordenadas > latitud").text(),
                        altitud: $hito.find("coordenadas > altitud").text()
                    },
                    distancia: distanciaElem.text(),
                    unidad: distanciaElem.attr("unidad"),
                    fotografias: fotografias
                });
            });
            
            // Procesar referencias
            const referencias = [];
            $ruta.find("referencias > referencia").each((_, refElem) => {
                referencias.push($(refElem).text());
            });
            
            // Crear objeto de ruta
            const nuevaRuta = {
                nombre: $ruta.find("> nombre").text(),
                tipo: $ruta.find("> tipo").text(),
                transporte: $ruta.find("> transporte").text(),
                fechaInicio: $ruta.find("> fechaInicio").text(),
                horaInicio: $ruta.find("> horaInicio").text(),
                duracion: $ruta.find("> duracion").text(),
                agencia: $ruta.find("> agencia").text(),
                descripcion: $ruta.find("> descripcion").text(),
                personasAdecuadas: $ruta.find("> personasAdecuadas").text(),
                puntoInicio: puntoInicio,
                hitos: hitos,
                referencias: referencias,
                recomendacion: $ruta.find("> recomendacion").text()
            };
            
            rutas.push(nuevaRuta);
        });
    }
    
    // Función auxiliar para escapar HTML
    function escapeHTML(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    
    // Función para cargar desde archivo
    function cargarDesdeArchivo(archivo) {
        const reader = new FileReader();
        reader.onload = (e) => {
            try {
                const xmlDoc = $.parseXML(e.target.result);
                const $xml = $(xmlDoc);
                
                // Mostrar el XML completo como texto
                rutasContainer.html(`
                    <section name="xml-content">
                        <h3>Contenido del archivo XML</h3>
                        <pre style="overflow-x: auto; white-space: pre-wrap; word-wrap: break-word;">${escapeHTML(e.target.result)}</pre>
                    </section>
                `);
                
                // Procesamiento normal del XML
                procesarXML($xml);
                mostrarRutas();
                
            } catch (error) {
                mostrarError("Error al procesar el archivo XML: " + error.message);
            }
        };
        reader.onerror = () => {
            mostrarError("Error al leer el archivo");
        };
        reader.readAsText(archivo);
    }
    
    // Evento de clic en el botón de procesar
    processButton.on("click", function() {
        if (fileInput[0].files.length === 0) {
            mostrarError("Por favor, selecciona un archivo XML");
            return;
        }
        
        const archivo = fileInput[0].files[0];
        if (archivo.type !== "text/xml" && !archivo.name.endsWith('.xml')) {
            mostrarError("El archivo debe ser de tipo XML");
            return;
        }
        
        cargarDesdeArchivo(archivo);
    });
    
    // También permitir cargar archivo al cambiar el input
    fileInput.on("change", function() {
        if (this.files.length > 0) {
            const archivo = this.files[0];
            if (archivo.type === "text/xml" || archivo.name.endsWith('.xml')) {
                cargarDesdeArchivo(archivo);
            } else {
                mostrarError("El archivo debe ser de tipo XML");
            }
        }
    });
});