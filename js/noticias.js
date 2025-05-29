class Noticias {
    constructor() {
        this.apiKey = "dcecce9e7a6b26ac005802d18b6c103d";
        this.query = "Oviedo";
        this.apiUrl = `https://gnews.io/api/v4/search?q=${encodeURIComponent(this.query)}&lang=es&country=es&max=5&apikey=${this.apiKey}`;
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
        this.$seccion.append('<p>⏳ Cargando noticias...</p>');
        
        fetch(this.apiUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error en la respuesta: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                this.$seccion.empty();
                const $titulo = $('<h2>').text('Actualidad del concejo');
                const $icono = $('<span>').text('📰 ');
                $titulo.prepend($icono);
                this.$seccion.append($titulo);
                
                if (data.articles && data.articles.length > 0) {
                    data.articles.forEach((article, index) => {
                        const titulo = article.title;
                        const enlace = article.url;
                        const descripcion = article.description;
                        const imagen = article.image || '';
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
                            const $figure = $('<figure>'); // Reemplazado div por figure
                            const $img = $('<img>').attr({
                                'src': imagen,
                                'alt': titulo
                            });
                            $figure.append($img);
                            $articulo.append($figure);
                        } else {
                            const $figure = $('<figure>'); // Reemplazado div por figure
                            const $iconoNoticia = $('<span>').text('📢').css({
                                'font-size': '2em',
                                'display': 'block',
                                'text-align': 'center'
                            });
                            $figure.append($iconoNoticia);
                            $articulo.append($figure);
                        }
                        
                        const $pDesc = $('<p>').text(descripcion);
                        $articulo.append($pDesc);
                        
                        const $seccionDatos = $('<section>'); // Reemplazado div por section
                        
                        const $pFecha = $('<p>');
                        $pFecha.html(`📅 Fecha: <span>${fecha}</span>`);
                        $seccionDatos.append($pFecha);
                        
                        const $pFuente = $('<p>');
                        $pFuente.html(`🔍 Fuente: <span>${article.source.name}</span>`);
                        $seccionDatos.append($pFuente);
                        
                        $articulo.append($seccionDatos);
                        
                        $articulo.css({
                            'border-bottom': '1px solid #ccc',
                            'padding-bottom': '20px',
                            'margin-bottom': '20px'
                        });
                        
                        if (index === data.articles.length - 1) {
                            $articulo.css('border-bottom', 'none');
                        }
                        
                        this.$seccion.append($articulo);
                    });
                    
                    const fechaActualizacion = new Date().toLocaleDateString('es-ES', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    
                    const $actualizacion = $('<footer>').css({ // Reemplazado p por footer
                        'font-style': 'italic',
                        'margin-top': '30px',
                        'padding-top': '10px',
                        'border-top': '1px solid #eee'
                    }).html(`<strong>Última actualización:</strong> ${fechaActualizacion}`);
                    
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