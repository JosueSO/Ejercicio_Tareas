const listaTareas = document.getElementById('task-list');
const listaCategorias = document.getElementById('categories_list');

let categorias = [];
//Listeners
eventListener();

function eventListener() {
    //Cambia categoría
    listaCategorias.addEventListener("change", getTareas);

    //Contenido cargado
    document.addEventListener("DOMContentLoaded", documentListo);
}

//Funciones

//Obtener las tareas en base a los filtros
function getCategorias() {
    var xhttp = new XMLHttpRequest();

    xhttp.open("GET", "./Controllers/categoriasController.php", true);
    xhttp.onload = function() {
        if (this.status == 200) {
            //console.log(this.responseText);

            categorias = JSON.parse(this.responseText);
            var html = "";
            categorias.forEach(categoria => {
                html += 
                    `<option value="${categoria.id}">${categoria.nombre}</option>`;
            });

            listaCategorias.innerHTML += html;
        }
    };

    xhttp.send();
}

//Obtener las tareas en base a los filtros
function getTareas() {
    var xhttp = new XMLHttpRequest();

    var categoria_id = listaCategorias.value;
    if (categoria_id != 0) {
        xhttp.open("GET", "./Controllers/tareasController.php?categoria_id=" + categoria_id, true);
    }
    else {
        xhttp.open("GET", "./Controllers/tareasController.php", true);
    }

    xhttp.onload = function() {
        if (this.status == 200) {
            //console.log(this.responseText);

            var tareas = JSON.parse(this.responseText);
            var html = "";
            let categoria;
            let fecha_limite = "";
            let descripcion = "";
            tareas.forEach(tarea => {
                categoria = categorias.find(c => c.id == tarea.categoria_id);
                fecha_limite = tarea.fecha_limite !== null ? " - "  + tarea.fecha_limite : "";
                descripcion = tarea.descripcion !== null ? tarea.descripcion : "";
                html += 
                    `<div class="task">
                        <div class="task-date">${categoria.nombre}${fecha_limite}</div>
                        <h2 class="m-0">${tarea.titulo}</h2>
                        <div class="text-justify mt-2">${descripcion}</div>
                    </div>`;
            });
            
            listaTareas.innerHTML = html;
        }
    };

    xhttp.send();
}

//Cargar Tareas en la lista
function documentListo() {
    getCategorias();
    getTareas();
}