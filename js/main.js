window.addEventListener("DOMContentLoaded", async (event) => {
	await listarPendientes();
	await listarTerminados();
	let guardarTicket = document.getElementById("guardar");
	let modalTicket = document.getElementById("modal_tickets");
	$("#nuevoTicket").on("click", function () {
		$("#modal_tickets .modal-title").text("Nuevo ticket");
		$("#modal_tickets .modal-body #descripcion").val("");
	});
	$("#tbl_tickets_pendientes tbody").on("click", ".editarR", function () {
		let Idorden = this.value;
		let requerimiento = this.parentNode.parentNode.childNodes[2].innerHTML;
		$("#modal_tickets .modal-title").text("Editar ticket - " + Idorden);
		$("#modal_tickets .modal-body #descripcion").val(requerimiento);
		$(modalTicket).modal("show");
	});
	$("#tbl_tickets_pendientes tbody").on("click", ".cancelarR", function () {
		let Idorden = this.value;
		let requerimiento = this.parentNode.parentNode.childNodes[2].innerHTML;
		var trBorrar = this.parentNode.parentNode;
		alertify.confirm(
			"Cancelar",
			"Esta seguro que desea <b>cancelar</b> el ticket " +
				Idorden +
				"?<hr><small style='margin-top: 10px'>(" +
				requerimiento +
				")</small>",
			async function () {
				Idorden = Idorden.replace("*","");
				await cancelarRequerimiento(Idorden, trBorrar);
			},
			function () {
				alertify.error("Operación Cancelada");
			}
		);
	});
	guardarTicket.addEventListener("click", async function () {
		let ta_descripcion = document.getElementById("descripcion");
		let descripcion = ta_descripcion.value;
		ta_descripcion.value = "";
		if ($("#modal_tickets .modal-title").text() === "Nuevo ticket") {
			let fecha = getFechaHora().fecha2;
			let estado = 0;
			let nuevoRequerimiento = {
				requerimiento: descripcion,
				fecha: fecha,
				Idorden: null,
				fechaprometido: "",
				estado: estado,
			};
			await agregarNuevoRequerimiento(nuevoRequerimiento);
		} else {
			let Idorden = $("#modal_tickets .modal-title")
				.text()
				.replace("Editar ticket - ", "");
			let tr = $("#tbl_tickets_pendientes tbody button[value='"+Idorden+"']")[0].parentNode.parentNode;
			Idorden = Idorden.replace("*","");
			await actualizarRequerimiento(Idorden, descripcion, tr);
		}
		$(modalTicket).modal("hide");
	});
	$("#tbl_tickets_pendientes table tbody").sortable();
	$("#tbl_tickets_pendientes table tbody").disableSelection();
	$("#cambiarPrioridad").on('click', async function (){
		await establecerPrioridad();
	});
});

function listarPendientes() {
	return new Promise((exito) => {
		$.ajax({
			type: "POST",
			url: "scripts/apirequerimientos.php",
			data: { param: 1 },
			dataType: "json",
			success: function (response) {
				if (response.exito) {
					if (response.encontrados > 0) {
						cargarTablaPendientes(response[0]);
						exito(response.exito);
					} else {
						exito(response.exito);
					}
				}
			},
			error: function (response) {
				console.error(response);
			},
		});
	});
}

function cargarTablaPendientes(pendientes) {
	let tablaPendientes = document.querySelectorAll(
		"#tbl_tickets_pendientes table tbody"
	)[0];
	tablaPendientes.innerHTML = "";
	pendientes.forEach((pendiente) => {
		cargarFilaPendiente(pendiente);
	});
}

function cargarFilaPendiente(objeto) {
	let tablaPendientes = document.querySelectorAll(
		"#tbl_tickets_pendientes table tbody"
	)[0];
	let fecha = objeto.fecha.replace(
		/([0-9]{4})-([0-9]{2})-([0-9]{2})/,
		"$3/$2/$1"
	);
	let descripcion = objeto.requerimiento;
	let estimada = objeto.fechaprometido===null?"":objeto.fechaprometido.replace(
		/([0-9]{4})-([0-9]{2})-([0-9]{2})/,
		"$3/$2/$1"
	);
	let estado =
		objeto.estado === 1
			? "Pendiente"
			: objeto.estado === 0
			? "Nuevo"
			: objeto.estado;
	let tr = document.createElement("tr");
	let tdFecha = document.createElement("td");
	tdFecha.innerHTML = fecha;
	tr.appendChild(tdFecha);
	let tdOrden = document.createElement("td");
	tdOrden.innerHTML = objeto.Idorden===null?"":objeto.Idorden;
	tr.appendChild(tdOrden);
	let tdDescripcion = document.createElement("td");
	tdDescripcion.innerHTML = descripcion;
	tr.appendChild(tdDescripcion);
	let tdEstimada = document.createElement("td");
	tdEstimada.innerHTML = estimada;
	tr.appendChild(tdEstimada);
	let tdEstado = document.createElement("td");
	tdEstado.innerHTML = estado;
	if (estado === "Nuevo") {
		tdEstado.classList.add("font-weight-bold", "font-italic", "text-warning");
	}
	tr.appendChild(tdEstado);
	let tdEditar = document.createElement("td");
	let btnEditar = document.createElement("button");
	btnEditar.classList.add("btn", "btn-outline-info", "btn-sm", "editarR");
	btnEditar.innerHTML = '<i class="fas fa-edit"></i>';
	btnEditar.value = objeto.Idorden===null?objeto.uuid:objeto.Idorden;
	tdEditar.appendChild(btnEditar);
	tr.appendChild(tdEditar);
	let tdCancelar = document.createElement("td");
	let btnCancelar = document.createElement("button");
	btnCancelar.classList.add("btn", "btn-outline-danger", "btn-sm", "cancelarR");
	btnCancelar.innerHTML = '<i class="fas fa-trash-alt"></i>';
	btnCancelar.value = objeto.Idorden===null?objeto.uuid:objeto.Idorden;
	tdCancelar.appendChild(btnCancelar);
	tr.appendChild(tdCancelar);
	tr.classList.add("ui-sortable-handle");
	tablaPendientes.appendChild(tr);
}

function agregarNuevoRequerimiento(nuevoRequerimiento) {
	let prioridad = ($('#tbl_tickets_pendientes table tbody tr').length)+1;
	return new Promise((exito) => {
		$.ajax({
			type: "POST",
			url: "scripts/apirequerimientos.php",
			data: { param: 3, fecha: nuevoRequerimiento.fecha, requerimiento: nuevoRequerimiento.requerimiento, estado: nuevoRequerimiento.estado, prioridad},
			dataType: "json",
			success: function (response) {
				nuevoRequerimiento.id_requerimiento = response.id_requerimiento;
				nuevoRequerimiento.uuid = response.uuid;
				cargarFilaPendiente(nuevoRequerimiento);
				exito(response.exito);
			},
			error: function (response) {
				console.error(response);
				exito(response.exito);
			},
		});
	});
}

function actualizarRequerimiento(Idorden, descripcion,tr){
	return new Promise((exito)=>{
		$.ajax({
			type: "POST",
			url: "scripts/apirequerimientos.php",
			data: {'param':4, 'Idorden':Idorden, requerimiento: descripcion },
			dataType: "json",
			success: function(response){
				if(response.exito){
					tr.childNodes[2].innerHTML = descripcion;
					exito(response.exito);
				}else{
					console.error(response.msg);
					exito(response.exito);
				}
			},
			error: function(response){
				console.error(response);
				exito(false);
			}
		});
	});
}

function cancelarRequerimiento(Idorden, tr){
	return new Promise((exito)=>{
		$.ajax({
			type: "POST",
			url: "scripts/apirequerimientos.php",
			data: {param: 5, Idorden},
			dataType: "json",
			success: function(response){
				if(response.exito){
					alertify.success("Cancelado con exito");
					$(tr).remove();
					exito(response.exito);
				}else{
					console.error(response.msg);
					exito(response.exito);
				}
			},
			error: function(response){
				console.error(response);
				exito(false);
			}
		});
	});
}

function establecerPrioridad(){
	return new Promise((exito)=>{
		let trs = $("#tbl_tickets_pendientes tbody tr");
		let trsL = trs.length;
		let prioridades = [];
		for (let i = 0; i < trsL; i++){
			let tr = trs[i];
			let Idorden = tr.childNodes[1].innerHTML.replace("*","");
			let requerimiento = {'Idorden': Idorden, 'prioridad':(i+1)};
			prioridades.push(requerimiento);
		}
		$.ajax({
			type: "POST",
			url: "scripts/apirequerimientos.php",
			data: {param:6, prioridades: prioridades},
			dataType: "json",
			success: function(response){
				if(response.exito){
					alertify.success("Prioridades establecidas con exito.")
					exito(response.exito);
				}else{
					console.error(response.msg);
					exito(response.exito);
				}
			},
			error: function(response){
				console.error(response);
				exito(false);
			}
		});
	});
}

function listarTerminados() {
	return new Promise((exito) => {
		$.ajax({
			type: "POST",
			url: "scripts/apirequerimientos.php",
			data: { param: 2 },
			dataType: "json",
			success: function (response) {
				if (response.exito) {
					if (response.encontrados > 0) {
						cargarTablaEnCurso(response[0]);
						exito(response.exito);
					} else {
						exito(response.exito);
					}
				}
			},
			error: function (response) {
				console.error(response);
			},
		});
	});
}

function cargarTablaEnCurso(terminados) {
	terminados.forEach((terminado) => {
		cargarFilaEnCurso(terminado);
	});
}

function cargarFilaEnCurso(objeto) {
	let tablaEnCurso = document.querySelectorAll(
		"#tbl_tickets_encurso table tbody"
	)[0];
	let fecha = objeto.fecha.replace(
		/([0-9]{4})-([0-9]{2})-([0-9]{2})/,
		"$3/$2/$1"
	);
	let orden = objeto.Idorden;
	let descripcion = objeto.requerimiento;
	let estimada = objeto.fechaprometido.replace(
		/([0-9]{4})-([0-9]{2})-([0-9]{2})/,
		"$3/$2/$1"
	);
	let estado =
		objeto.estado === 2
			? "En Curso"
			: objeto.estado === 3
			? "Terminado"
			: objeto.estado === 5
			? "Implementado"
			: objeto.estado;
	let tr = document.createElement("tr");
	let tdFecha = document.createElement("td");
	tdFecha.innerHTML = fecha;
	tr.appendChild(tdFecha);
	let tdOrden = document.createElement("td");
	tdOrden.innerHTML = orden;
	tr.appendChild(tdOrden);
	let tdDescripcion = document.createElement("td");
	tdDescripcion.innerHTML = descripcion;
	tr.appendChild(tdDescripcion);
	let tdEstimada = document.createElement("td");
	tdEstimada.innerHTML = estimada;
	tr.appendChild(tdEstimada);
	let tdEstado = document.createElement("td");
	tdEstado.innerHTML = estado;
	tr.appendChild(tdEstado);
	tablaEnCurso.appendChild(tr);
}

function getFechaHora(diahr = new Date()) {
	var year = diahr.getFullYear().toString();
	var mes = (diahr.getMonth() + 1).toString().padStart(2, 0);
	var dia = diahr.getDate().toString().padStart(2, 0);
	var fecha = year + "-" + mes + "-" + dia;
	var fecha2 = dia + "/" + mes + "/" + year;
	var hora = diahr.getHours().toString().padStart(2, 0);
	var min = diahr.getMinutes().toString().padStart(2, 0);
	var seg = diahr.getSeconds().toString().padStart(2, 0);
	var time = hora + ":" + min + ":" + seg;
	return { fecha: fecha, hora: time, fecha2: fecha2 };
}
