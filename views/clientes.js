const API = "../index.php";

const formulario = document.getElementById("formCliente");
const idInput = document.getElementById("id");
const nombre = document.getElementById("nombre");
const apellido = document.getElementById("apellido");
const cedula = document.getElementById("cedula");
const telefono = document.getElementById("telefono");
const correo = document.getElementById("correo");
const tablahtml = document.getElementById("tablaClientes");
const mensaje = document.getElementById("mensaje");
const mensajeFormulario = document.getElementById("mensajeFormulario");
const botonGuardar = document.getElementById("botonGuardar");
const botonCancelar = document.getElementById("botonCancelar");
const tituloFormulario = document.getElementById("tituloFormulario");
const botonCrearTabla = document.getElementById("crearTabla");

function mostrarMensaje(texto, tipo) {
    mensaje.className = "alert alert-" + tipo + " mb-4";
    mensaje.textContent = texto;
}

function mostrarMensajeFormulario(texto, tipo) {
    mensajeFormulario.className = "alert alert-" + tipo + " mt-3 mb-0";
    mensajeFormulario.textContent = texto;
}

async function todos() {
    try{
        const respuesta = await fetch(API + "?accion=todos");
        const clientes = await respuesta.json();
        if(!respuesta.ok){
            throw new Error(clientes.error || "Error al obtener los datos");
        }
        tablahtml.innerHTML = "";
        clientes.forEach(cliente => {
            const fila = document.createElement("tr");

            const valores = [
                cliente.id,
                cliente.nombre,
                cliente.apellido,
                cliente.cedula,
                cliente.telefono,
                cliente.correo
            ];

            valores.forEach(valor => {
                const celda = document.createElement("td");
                celda.textContent = valor;
                fila.appendChild(celda);
            });

            const celdaAcciones = document.createElement("td");

            const botonEditar = document.createElement("button");
            botonEditar.type = "button";
            botonEditar.className = "btn btn-warning btn-sm me-2";
            botonEditar.textContent = "Editar";
            botonEditar.addEventListener("click", () => editar(cliente.id));

            const botonEliminar = document.createElement("button");
            botonEliminar.type = "button";
            botonEliminar.className = "btn btn-danger btn-sm";
            botonEliminar.textContent = "Eliminar";
            botonEliminar.addEventListener("click", () => eliminar(cliente.id));

            celdaAcciones.appendChild(botonEditar);
            celdaAcciones.appendChild(botonEliminar);
            fila.appendChild(celdaAcciones);

            tablahtml.appendChild(fila);
        });
    }catch(error){
        mostrarMensaje(error.message, "danger");
    }
}

function modoEdicion(activar) {
    if (activar) {
        tituloFormulario.textContent = "Actualizar Cliente";
        botonGuardar.textContent = "Actualizar";
        botonCancelar.classList.remove("d-none");
    } else {
        tituloFormulario.textContent = "Registrar Cliente";
        botonGuardar.textContent = "Guardar";
        botonCancelar.classList.add("d-none");
        formulario.reset();
        mensajeFormulario.className = "alert d-none mt-3 mb-0";
        mensajeFormulario.textContent = "";
    }
}

async function editar(id) {
    try {
        const respuesta = await fetch(API + "?accion=uno&id=" + id);
        const cliente = await respuesta.json();
        if (!respuesta.ok) {
            throw new Error(cliente.error || "Error al obtener el cliente");
        }

        idInput.value = cliente.id;
        nombre.value = cliente.nombre;
        apellido.value = cliente.apellido;
        cedula.value = cliente.cedula;
        telefono.value = cliente.telefono;
        correo.value = cliente.correo;

        modoEdicion(true);
    } catch (error) {
        mostrarMensajeFormulario(error.message, "danger");
    }
}

async function eliminar(id) {
    if (!confirm("¿Está seguro de eliminar este cliente?")) {
        return;
    }

    const datos = new URLSearchParams();
    datos.append("id", id);

    try {
        const respuesta = await fetch(
            API + "?accion=eliminar",
            {
                method: "POST",
                body: datos
            }
        );

        const resultado = await respuesta.json();
        if (!respuesta.ok) {
            throw new Error(resultado.error || "Error al eliminar");
        }

        mostrarMensaje("Cliente eliminado correctamente", "success");
        await todos();
    } catch (error) {
        mostrarMensaje(error.message, "danger");
    }
}

formulario.addEventListener("submit", async function(event) {

    event.preventDefault();

    const id = idInput.value.trim();

    const datos = new URLSearchParams();
    if (id !== "") {
        datos.append("id", id);
    }
    datos.append("nombre", nombre.value.trim());
    datos.append("apellido", apellido.value.trim());
    datos.append("cedula", cedula.value.trim());
    datos.append("telefono", telefono.value.trim());
    datos.append("correo", correo.value.trim());

    const accion = id === "" ? "guardar" : "actualizar";
    const mensajeExito = id === "" ? "Cliente guardado correctamente" : "Cliente actualizado correctamente";

    try {
        const respuesta = await fetch(
            API + "?accion=" + accion,
            {
                method: "POST",
                body: datos
            }
        );

        const resultado = await respuesta.json();
        if (!respuesta.ok) {
            throw new Error(resultado.error || "Error al guardar");
        }

        const exito = resultado.guardado ?? resultado.actualizado;

        if (exito) {
            modoEdicion(false);
            mostrarMensajeFormulario(mensajeExito, "success");
            await todos();
        } else {
            mostrarMensajeFormulario("No se pudo completar la operación", "danger");
        }

    } catch (error) {
        mostrarMensajeFormulario(error.message, "danger");
    }
});

botonCancelar.addEventListener("click", () => modoEdicion(false));

async function crearTabla() {
    try {
        const respuesta = await fetch(API + "?accion=creartabla", {
            method: "POST"
        });
        const resultado = await respuesta.json();
        if (!respuesta.ok || !resultado.tabla_creada) {
            throw new Error(resultado.error || "No se pudo crear la tabla");
        }
        if (resultado.ya_existia) {
            mostrarMensaje("Notificación: la tabla clientes ya existía. No se creó de nuevo y los datos se conservaron.", "warning");
        } else {
            mostrarMensaje("Tabla creada correctamente.", "success");
        }
        await todos();
    } catch (error) {
        mostrarMensaje(error.message, "danger");
    }
}

botonCrearTabla.addEventListener("click", crearTabla);

todos();
