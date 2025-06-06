class Noticias {
    constructor() {
        this.apiKey = "02e2c3f6c0674428b86d20ad11d1d514";
        this.query = "\"Muros del Nalón\" OR \"Soto del Barco\" OR \"Pravia\" OR \"Cudillero\" OR \"Candamo\"";
        this.apiUrl = `https://newsapi.org/v2/everything?q=${encodeURIComponent(this.query)}&from=2025-04-30&sortBy=publishedAt&language=es&apiKey=${this.apiKey}`;
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
                
                if (data.articles && data.articles.length > 0) {
                    data.articles.forEach((article, index) => {
                        const titulo = article.title;
                        const enlace = article.url;
                        const descripcion = article.description;
                        const imagen = article.urlToImage || '';
                        const fecha = new Date(article.publishedAt).toLocaleDateString('es-ES', { 
                            weekday: 'long', 
                            day: 'numeric', 
                            month: 'long' 
                        });
                        
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
                        
                        const $pFuente = $('<p>');
                        $pFuente.html(`🔍 Fuente: <span>${article.source.name}</span>`);
                        $seccionDatos.append($pFuente);
                        
                        if (article.author) {
                            const $pAutor = $('<p>');
                            $pAutor.html(`✍️ Autor: <span>${article.author}</span>`);
                            $seccionDatos.append($pAutor);
                        }

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