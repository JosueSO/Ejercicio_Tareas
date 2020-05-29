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

    xhttp.open("GET", api + "categorias", false);

    xhttp.send();

    var data = JSON.parse(xhttp.responseText);

    if (data.success === true){
        categorias = data.data;
        var html = "";
        categorias.forEach(categoria => {
            html += 
                `<option value="${categoria.id}">${categoria.nombre}</option>`;
        });

        listaCategorias.innerHTML += html;
    }
    else {
        alert(data.messages);
    }
}

//Obtener las tareas en base a los filtros
function getTareas() {
    var sesion = getSesion();

    if (sesion == null) {
        window.location.href = client;
    }

    var xhttp = new XMLHttpRequest();

    var categoria_id = listaCategorias.value;
    if (categoria_id != 0) {
        xhttp.open("GET", api + "tareas/categoria_id=" + categoria_id, true);
    }
    else {
        xhttp.open("GET", api + "tareas", true);
    }

    xhttp.setRequestHeader("Authorization", sesion.token_acceso);

    xhttp.onload = function() {
        if (this.status == 200) {
            var html = "";
            var data = JSON.parse(this.responseText);

            if (data.success == true) {
                var tareas = data.data.tareas;
                
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
            }
            listaTareas.innerHTML = html;
        }
        else if(this.status == 401) {
            var data = JSON.parse(this.responseText);

            if (data.messages.indexOf("Token de acceso ha caducado") >= 0) {
                refreshToken();
                window.location.reload();
            }
        }
        else {
            var data = JSON.parse(this.responseText);

            alert(data.messages);
        }
    };

    xhttp.send();
}

//Cargar Tareas en la lista
function documentListo() {
    getCategorias();
    getTareas();
}