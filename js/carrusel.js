class Carrusel {
    constructor(imagenes) {
        this.imagenes = imagenes;
        this.indice = 0;
    }

    crearCarrusel() {
        const $carrusel = $('<section>').attr('title', 'Carrusel de fotos');
        const $figure = $('<figure>');

        const $img = $('<img>').attr('src', this.imagenes[this.indice]).attr('alt', 'Foto carrusel');
        
        const $controles = $('<form>');
        const $botonPrev = $('<button>').attr('type', 'button').text('Anterior');
        const $botonNext = $('<button>').attr('type', 'button').text('Siguiente');

        this.$img = $img;

        $botonPrev.on('click', () => this.anterior());
        $botonNext.on('click', () => this.siguiente());

        const $contador = $('<span>');
        this.$contador = $contador;
        this.actualizarContador();
        
        $controles.append($botonPrev, $contador, $botonNext);
        $figure.append($img);
        $carrusel.append($figure, $controles);

        $('main').append($carrusel);
        
        const $separador = $('<hr>').attr('aria-hidden', 'true');
        $('main').append($separador);
    }

    actualizarContador() {
        this.$contador.text(` ${this.indice + 1} de ${this.imagenes.length} `);
    }

    mostrarImagen() {
        this.$img.attr('src', this.imagenes[this.indice]);
        this.actualizarContador();
    }

    anterior() {
        this.indice = (this.indice - 1 + this.imagenes.length) % this.imagenes.length;
        this.mostrarImagen();
    }

    siguiente() {
        this.indice = (this.indice + 1) % this.imagenes.length;
        this.mostrarImagen();
    }
}

$(function() {
    const imagenes = [
        'multimedia/muros1.jpg',
        'multimedia/muros2.jpg',
        'multimedia/muros3.jpg',
        'multimedia/muros4.jpg', 
        'multimedia/mapaMuros.jpg'
    ];

    const carrusel = new Carrusel(imagenes);
    carrusel.crearCarrusel();
});