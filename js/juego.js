const preguntas = [
    {
        pregunta: "¿Cuál es la especialidad gastronómica típica de Muros del Nalón?",
        opciones: [
            "Fabas con almejas",
            "Paella",
            "Gazpacho",
            "Cocido madrileño",
            "Pulpo a la gallega"
        ],
        correcta: 0
    },
    {
        pregunta: "¿Qué ruta famosa se puede hacer en Muros del Nalón?",
        opciones: [
            "Ruta del Cares",
            "Ruta de los Miradores",
            "Camino de Santiago",
            "Ruta del Quijote",
            "Ruta de la Plata"
        ],
        correcta: 1
    },
    {
        pregunta: "¿Qué río desemboca cerca de Muros del Nalón?",
        opciones: [
            "Nalón",
            "Sella",
            "Eo",
            "Navia",
            "Deva"
        ],
        correcta: 0
    },
    {
        pregunta: "¿Qué playa es característica de Muros del Nalón?",
        opciones: [
            "Playa de las Catedrales",
            "Playa de Aguilar",
            "Playa de Rodiles",
            "Playa de San Lorenzo",
            "Playa de Gulpiyuri"
        ],
        correcta: 1
    },
    {
        pregunta: "¿Qué edificio destaca en el patrimonio de Muros del Nalón?",
        opciones: [
            "Catedral de Oviedo",
            "Palacio de Valdecarzana",
            "Iglesia de Santa María",
            "Universidad Laboral",
            "Castillo de San Antón"
        ],
        correcta: 2
    },
    {
        pregunta: "¿Qué actividad se recomienda en la zona?",
        opciones: [
            "Esquí",
            "Senderismo",
            "Surf en el Mediterráneo",
            "Visitar museos de arte moderno",
            "Escalada en roca"
        ],
        correcta: 1
    },
    {
        pregunta: "¿Cuál es el gentilicio de los habitantes de Muros del Nalón?",
        opciones: [
            "Muroso",
            "Naloneses",
            "Murense",
            "Murense de Nalón",
            "Murense o murense de Nalón"
        ],
        correcta: 4
    },
    {
        pregunta: "¿Qué evento meteorológico es frecuente en la zona?",
        opciones: [
            "Tormentas de arena",
            "Niebla y lluvias",
            "Sequías extremas",
            "Tornados",
            "Nevadas intensas"
        ],
        correcta: 1
    },
    {
        pregunta: "¿Qué se puede reservar en la web?",
        opciones: [
            "Entradas para conciertos",
            "Visitas guiadas y alojamientos",
            "Vuelos internacionales",
            "Cursos de surf",
            "Excursiones a la montaña"
        ],
        correcta: 1
    },
    {
        pregunta: "¿Para que universidad es este proyecto?",
        opciones: [
            "Universidad de Salamanca",
            "Universidad de Oviedo",
            "Universidad de León",
            "Universidad de Cantabria",
            "Universidad de Vigo"
        ],
        correcta: 1
    }
];

function barajar(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
}

function crearJuego() {
    const footer = document.getElementsByTagName('footer')[0];
    const form = document.createElement('form');
    
    const preguntasBarajadas = preguntas.slice();
    barajar(preguntasBarajadas);

    preguntasBarajadas.forEach(function(preg, i) {
        const fieldset = document.createElement('fieldset');
        const legend = document.createElement('legend');
        legend.textContent = (i + 1) + '. ' + preg.pregunta;
        fieldset.appendChild(legend);
        preg.opciones.forEach(function(opcion, j) {
            const inputId = 'pregunta' + i + '_opcion' + j;
            const input = document.createElement('input');
            input.type = 'radio';
            input.name = 'pregunta' + i;
            input.value = j;
            input.id = inputId;
            
            const label = document.createElement('label');
            label.htmlFor = inputId;
            label.appendChild(document.createTextNode(' ' + opcion));
            
            fieldset.appendChild(input);
            fieldset.appendChild(label);
            fieldset.appendChild(document.createElement('br'));
        });
        form.appendChild(fieldset);
    });

    const botonId = 'btn_enviar';
    const boton = document.createElement('button');
    boton.type = 'submit';
    boton.textContent = 'Enviar respuestas';
    boton.id = botonId;
    
    const botonLabel = document.createElement('label');
    botonLabel.htmlFor = botonId;
    botonLabel.hidden = true;
    botonLabel.textContent = 'Botón para enviar respuestas del cuestionario';
    
    form.appendChild(botonLabel);
    form.appendChild(boton);

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        let aciertos = 0;
        let contestadas = 0;
        preguntasBarajadas.forEach(function(preg, i) {
            const seleccionada = form.querySelector('input[name="pregunta' + i + '"]:checked');
            if (seleccionada) {
                contestadas++;
                if (parseInt(seleccionada.value) === preg.correcta) aciertos++;
            }
        });
        if (contestadas < preguntasBarajadas.length) {
            alert('Debes responder todas las preguntas.');
            return;
        }
        mostrarResultado(aciertos, form, footer);
    });

    footer.parentNode.insertBefore(form, footer);
}

function mostrarResultado(puntos, form, footer) {
    form.remove();
    
    const resultado = document.createElement('section');
    
    // Crear elementos de forma individual en lugar de usar innerHTML
    const titulo = document.createElement('h2');
    titulo.textContent = '¡Juego finalizado!';
    
    const texto = document.createElement('p');
    texto.textContent = 'Has obtenido una puntuación de ';
    
    const puntuacion = document.createElement('strong');
    puntuacion.textContent = puntos;
    texto.appendChild(puntuacion);
    
    texto.appendChild(document.createTextNode(' sobre 10.'));
    
    const boton = document.createElement('button');
    boton.type = 'button';
    boton.textContent = 'Volver a jugar';
    boton.onclick = function() {
        resultado.remove();
        crearJuego();
    };
    
    // Añadir todos los elementos al section
    resultado.appendChild(titulo);
    resultado.appendChild(texto);
    resultado.appendChild(boton);
    
    footer.parentNode.insertBefore(resultado, footer);
}

document.addEventListener('DOMContentLoaded', crearJuego);