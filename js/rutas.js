class Rutas {
    constructor() {
        this.rutas = [];
        this.mapas = [];
        this.rutaActual = null;
    }

    cargarDesdeArchivo(archivo) {
        const reader = new FileReader();
        reader.onload = (e) => {
            try {
                const xmlDoc = $.parseXML(e.target.result);
                const $xml = $(xmlDoc);
                this.procesarXML($xml);
                this.mostrarSelectorRutas();
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
            
            this.rutas.push(nuevaRuta);
        });
    }

    mostrarSelectorRutas() {
        const $container = $("[name='rutasContainer']");
        $container.empty();
        
        if (this.rutas.length === 0) {
            $container.html("<p>No se encontraron rutas en el archivo XML.</p>");
            return;
        }
        
        // Añadir encabezado con el número de rutas encontradas
        $container.append(`<h2>Se han encontrado ${this.rutas.length} rutas</h2>`);
        
        // Crear selector de rutas
        const selectorHTML = `
            <section name="selector-rutas">
                <h3>Seleccionar ruta a visualizar:</h3>
                <select name="selector-ruta">
                    <option value="">Seleccione una ruta</option>
                    ${this.rutas.map((ruta, index) => 
                        `<option value="${index}">${ruta.nombre}</option>`
                    ).join('')}
                </select>
            </section>
            <section name="ruta-detalle"></section>
        `;
        
        $container.append(selectorHTML);
        
        // Escuchar cambios en el selector
        $("[name='selector-ruta']").on("change", (e) => {
            const selectedIndex = $(e.target).val();
            
            // Si hay un mapa activo, elimínalo
            if (this.mapaActual) {
                this.mapaActual.setTarget(null);
                this.mapaActual = null;
            }
            
            if (selectedIndex !== "") {
                this.mostrarRuta(parseInt(selectedIndex));
            } else {
                $("[name='ruta-detalle']").empty();
            }
        });
        
        // Información sobre el XML
        const fileInput = $("input[name='fileInput']")[0];
        const xmlInfo = `
            <section name="xml-info">
                <h3>Información del archivo XML</h3>
                <p>Nombre del archivo: ${fileInput.files[0].name}</p>
                <p>Tamaño: ${(fileInput.files[0].size / 1024).toFixed(2)} KB</p>
                <p>Fecha de carga: ${new Date().toLocaleString()}</p>
            </section>
        `;
        $container.append(xmlInfo);
    }
    
    mostrarRuta(index) {
        const ruta = this.rutas[index];
        const $detalleContainer = $("[name='ruta-detalle']");
        $detalleContainer.empty();
        
        // Guardar la ruta actual
        this.rutaActual = {
            nombre: ruta.nombre,
            altimetria: `altimetria_${this.normalizarNombre(ruta.nombre)}.svg`
        };
        
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
                    <p><strong>Coordenadas:</strong> ${this.formatearCoordenadas(ruta.puntoInicio?.coordenadas)}</p>
                </section>
                
                <section name="visualizaciones">
                    <section name="contenedorAltimetria">
                    </section>
                    <section name="mapa-container">
                        <h4>Mapa de la Ruta</h4>
                        <figure name="mapa-figura"></figure>
                    </section>
                </section>
                
                <section name="hitos-ruta">
                    ${this.mostrarHitos(ruta.hitos)}
                </section>
                
                ${this.mostrarReferencias(ruta.referencias)}
            </article>
        `;
        
        $detalleContainer.append(rutaHTML);
        
        // Cargar la altimetría usando AJAX
        this.cargarAltimetriaAJAX();
        
        // Inicializar el mapa después de que el DOM esté listo
        setTimeout(() => {
            const nombreArchivo = this.normalizarNombre(ruta.nombre);
            const kmlUrl = `xml/${nombreArchivo}.kml`;
            const mapElement = document.querySelector("[name='mapa-figura']");
            
            if (mapElement) {
                // Make sure container has dimensions using inline styles instead of class
                mapElement.setAttribute('style', 'width: 100%; height: 400px;');
                
                this.mapaActual = this.inicializarMapa(mapElement, kmlUrl);
            }
        }, 300);
    }
    
    formatearCoordenadas(coords) {
        if (!coords) return 'No disponibles';
        return `Latitud: ${coords.latitud || 'N/A'}, Longitud: ${coords.longitud || 'N/A'}, Altitud: ${coords.altitud || 'N/A'} m`;
    }
    
    normalizarNombre(nombre) {
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
    
    cargarAltimetriaAJAX() {
        // Verificar que exista la ruta actual
        if (!this.rutaActual || !this.rutaActual.altimetria) {
            console.error("No hay ruta actual para mostrar altimetría");
            return;
        }
        
        // Limpiar contenedor
        const $contenedor = $("[name='contenedorAltimetria']");
        $contenedor.empty();
        
        // Crear encabezado para la sección de altimetría
        const h4Alt = document.createElement('h4');
        h4Alt.textContent = 'Perfil de elevación';
        $contenedor.append(h4Alt);
        
        // Cargar archivo SVG
        $.ajax({
            url: "xml/" + this.rutaActual.altimetria,
            type: "GET",
            dataType: "text",
            success: (svgData) => {
                // Crear contenedor semántico para el SVG
                const contenedorSVG = document.createElement('figure');
                contenedorSVG.setAttribute('name', 'svg-container');
                
                // Añadir encabezado para el SVG
                const figcaption = document.createElement('figcaption');
                figcaption.textContent = 'Gráfica de altimetría';
                contenedorSVG.appendChild(figcaption);
                
                // Procesar el SVG para que se muestre correctamente
                let processedSvgData = svgData;
                
                // Asegurarse de que el SVG tenga atributos width y height al 100%
                if (!processedSvgData.includes('width="100%"')) {
                    processedSvgData = processedSvgData.replace(/<svg/, '<svg width="100%" height="auto"');
                }
                
                // Asegurarse de que el SVG tenga preserveAspectRatio
                if (!processedSvgData.includes('preserveAspectRatio')) {
                    processedSvgData = processedSvgData.replace(/<svg/, '<svg preserveAspectRatio="xMidYMid meet"');
                }
                
                // Añadir el SVG procesado al contenedor
                contenedorSVG.innerHTML += processedSvgData;
                
                // Añadir el contenedor al DOM
                $contenedor.append(contenedorSVG);
                
                // Asegurarnos de que el SVG se vea correctamente con atributos en línea
                const svgElement = contenedorSVG.querySelector('svg');
                if (svgElement) {
                    svgElement.setAttribute('style', 'max-width: 100%; height: auto; display: block; margin: 0 auto;');
                    
                    // Añadir atributos para asegurar que el SVG se escale correctamente
                    if (!svgElement.hasAttribute('viewBox')) {
                        const width = svgElement.getAttribute('width') || '100%';
                        const height = svgElement.getAttribute('height') || '300';
                        svgElement.setAttribute('viewBox', `0 0 ${width} ${height}`);
                    }
                }
                
                // Añadir estilos en línea al contenedor para asegurar visualización correcta
                contenedorSVG.setAttribute('style', 'width: 100%; overflow-x: auto; margin-bottom: 20px;');
            },
            error: (error) => {
                console.error('Error al cargar la altimetría:', error);
                const mensajeError = document.createElement('p');
                mensajeError.textContent = 'No se ha podido cargar el perfil de altimetría';
                $contenedor.append(mensajeError);
            }
        });
    }
    
    inicializarMapa(container, kmlUrl) {
        if (!container) {
            console.error('Map container not found');
            return null;
        }
        
        const map = new ol.Map({
            target: container,
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
    
    mostrarHitos(hitos) {
        if (!hitos || hitos.length === 0) return '<p>No hay hitos registrados para esta ruta.</p>';
        
        let hitosHTML = '<h4>Puntos de interés en la ruta:</h4>';
        
        hitos.forEach((hito, index) => {
            hitosHTML += `
                <article name="hito">
                    <h5>${index + 1}. ${hito.nombre}</h5>
                    <p>${hito.descripcion}</p>
                    <section name="hito-detalles">
                        <p><strong>Coordenadas:</strong> ${this.formatearCoordenadas(hito.coordenadas)}</p>
                        <p><strong>Distancia desde el inicio:</strong> ${hito.distancia} ${hito.unidad || ''}</p>
                    </section>
                    ${this.generarGaleriaFotos(hito.fotografias)}
                </article>
            `;
        });
        
        return hitosHTML;
    }
    
    generarGaleriaFotos(fotografias) {
        if (!fotografias || fotografias.length === 0) return '';
        
        let galeriaHTML = '<section name="galeria-fotos">';
        fotografias.forEach(foto => {
            galeriaHTML += `<img src="${foto}" alt="Fotografía de la ruta" />`;
        });
        galeriaHTML += '</section>';
        
        return galeriaHTML;
    }
    
    mostrarReferencias(referencias) {
        if (!referencias || referencias.length === 0) return '';
        
        let refHTML = '<section name="referencias"><h4>Referencias:</h4><ul>';
        referencias.forEach(ref => {
            refHTML += `<li><a href="${ref}" target="_blank">${ref}</a></li>`;
        });
        refHTML += '</ul></section>';
        
        return refHTML;
    }
    
    mostrarError(mensaje) {
        $("[name='rutasContainer']").html(`<p>${mensaje}</p>`);
    }
}

// Wrap initialization in DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    const rutasApp = new Rutas();
    
    $("[name='processFile']").on("click", function() {
        const fileInput = document.querySelector("input[name='fileInput']");
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
    
    // También permitir cargar archivo al cambiar el input
    $("input[name='fileInput']").on("change", function() {
        if (this.files.length > 0) {
            const archivo = this.files[0];
            if (archivo.type === "text/xml" || archivo.name.endsWith('.xml')) {
                rutasApp.cargarDesdeArchivo(archivo);
            } else {
                rutasApp.mostrarError("El archivo debe ser de tipo XML");
            }
        }
    });
});