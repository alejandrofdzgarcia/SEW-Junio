class Noticias {
    constructor() {
        this.apiKey = "pub_ac0aea91c526429e9e97857e80c835e0";
        this.query = "Asturias";
        this.apiUrl = `https://newsdata.io/api/1/latest?apikey=${this.apiKey}&q=${encodeURIComponent(this.query)}&language=es`;
    }

    crearSeccionNoticias() {
        const $seccion = $('<section>').attr('title', 'Noticias sobre Muros del Nalón');
        const $titulo = $('<h2>').text('Actualidad del concejo');
        
        const $icono = $('<span>').text('📰 ');
        $titulo.prepend($icono);
        
        $seccion.append($titulo);
        $('main').append($seccion);

        this.$seccion = $seccion;
        
        this.cargarNoticias();
    }

    cargarNoticias() {
        this.$seccion.append('<p>Cargando noticias...</p>');
        fetch(this.apiUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error en la respuesta: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                this.$seccion.empty();
                const $titulo = $('<h2>').text('Actualidad de Muros del Nalón y comarca');
                const $icono = $('<span>').text('📰 ');
                $titulo.prepend($icono);
                this.$seccion.append($titulo);
                
                if (data.results && data.results.length > 0) {
                    data.results.forEach((article, index) => {
                        const titulo = article.title;
                        const enlace = article.link;
                        const descripcion = article.description;
                        const imagen = article.image_url || '';
                        
                        // Convertir la fecha de pubDate al formato local
                        const fechaHora = new Date(article.pubDate);
                        const fecha = fechaHora.toLocaleDateString('es-ES', { 
                            weekday: 'long', 
                            day: 'numeric', 
                            month: 'long',
                            year: 'numeric'
                        });
                        const hora = fechaHora.toLocaleTimeString('es-ES', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        
                        // Obtener la categoría
                        const categoria = article.category && article.category.length > 0 ? 
                            article.category.join(', ') : 'Sin categoría';
                        
                        const $articulo = $('<article>');
                        
                        const $h3 = $('<h3>');
                        const $a = $('<a>').attr({
                            'href': enlace,
                            'target': '_blank'
                        }).text(titulo);
                        $h3.append($a);
                        $articulo.append($h3);
                        
                        if (imagen) {
                            const $figure = $('<figure>');
                            const $img = $('<img>').attr({
                                'src': imagen,
                                'alt': titulo
                            });
                            $figure.append($img);
                            $articulo.append($figure);
                        } else {
                            const $figure = $('<figure>');
                            const $iconoNoticia = $('<span>').text('📢');
                            $figure.append($iconoNoticia);
                            $articulo.append($figure);
                        }
                        
                        const $pDesc = $('<p>').text(descripcion);
                        $articulo.append($pDesc);
                        
                        const $seccionDatos = $('<section>');
                        
                        const $pFecha = $('<p>');
                        $pFecha.html(`📅 Fecha: <span>${fecha}</span>`);
                        $seccionDatos.append($pFecha);
                        
                        const $pHora = $('<p>');
                        $pHora.html(`🕒 Hora: <span>${hora}</span>`);
                        $seccionDatos.append($pHora);
                        
                        const $pCategoria = $('<p>');
                        $pCategoria.html(`📂 Categoría: <span>${categoria}</span>`);
                        $seccionDatos.append($pCategoria);
                        
                        const $pFuente = $('<p>');
                        $pFuente.html(`🔍 Fuente: <span>${article.source_name || 'Desconocida'}</span>`);
                        $seccionDatos.append($pFuente);

                        $articulo.append($seccionDatos);
                        
                        this.$seccion.append($articulo);
                    });
                    
                    const fechaActualizacion = new Date().toLocaleDateString('es-ES', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    
                    const $actualizacion = $('<footer>')
                        .html(`<strong>Última actualización:</strong> ${fechaActualizacion}`);
                    
                    this.$seccion.append($actualizacion);
                    
                } else {
                    this.$seccion.append('<p>No se encontraron noticias sobre Muros de Nalón.</p>');
                }
            })
            .catch(error => {
                console.error("Error al cargar noticias:", error);
                this.$seccion.empty();
                const $titulo = $('<h2>').text('Actualidad del concejo');
                const $icono = $('<span>').text('📰 ');
                $titulo.prepend($icono);
                this.$seccion.append($titulo);
                this.$seccion.append('<p>⚠️ Error al cargar las noticias. Por favor, intente más tarde.</p>');
            });
    }
}

$(document).ready(function() {
    const noticias = new Noticias();
    noticias.crearSeccionNoticias();
});