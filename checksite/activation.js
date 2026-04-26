let cfb_executing_validation = localStorage.getItem("cfb_executing_validation");
if (cfb_executing_validation === null) {
	cfb_executing_validation = "false";
	localStorage.setItem("cfb_executing_validation", cfb_executing_validation);
}

console.log("cfb_executing_validation: ", cfb_executing_validation);
cfb_executing_validation = cfb_executing_validation === "true";
console.log("cfb_executing_validation: ", cfb_executing_validation);

if (
	typeof cfb_executing_validation !== "undefined" &&
	cfb_executing_validation === false
) {
	console.log("SI existe cfb_executing_validation");
	//return;

	(async function (cfb_executing_validation) {
		console.log("Script en ejecucion en local");
		console.log("cfb_executing_validation: ", cfb_executing_validation);
		if (cfb_executing_validation) {
			return;
		}
		cfb_executing_validation = true;
		localStorage.setItem("cfb_executing_validation", cfb_executing_validation);
		try {
			let domain = window.location.host.replace("www.", "");
			let domainmodified = domain.replaceAll(/[\.,:]/g, "");
			let hoy = new Date();
			let body = null;
			let original_body = null;
			console.log("domain", domain);
			console.log("domainmodified", domainmodified);
			//console.log('hoy',hoy);
			document.addEventListener("DOMContentLoaded", () => {
				body = document.getElementsByTagName("body")[0];
				original_body = body.innerHTML;
				body.innerHTML = "<pre></pre>";
				//console.log("Document Ready");
			});
			const response = await fetch(
				`https://cristophernando.github.io/checksite/${domainmodified}.json?date=${hoy.getTime()}`,
			);

			// Check if the request was successful
			if (!response.ok) {
				body.innerHTML = original_body;
				localStorage.setItem("cfb_executing_validation", false);
				throw new Error(`HTTP error! status: ${response.status}`);
			}

			const data = await response.json(); // Parses JSON into a JavaScript object
			if (data["domain"] != domain) {
				//Proteccion en caso de error en archivo JSON
				body.innerHTML = original_body;
				localStorage.setItem("cfb_executing_validation", false);
				return;
			}
			//Se actualiza en base de datos para ya no preguntar al servidor
			if (data["keep_checking"] == false) {
				body.innerHTML = original_body;
				localStorage.setItem("cfb_executing_validation", false);
				return;
			}

			let expiration_date = new Date(data["expiration_date"]);
			let balance = data["balance"];
			let title = data["title"];
			let message = data["message"];
			let invoice = data["invoice"];
			//console.log('expiration_date',expiration_date);
			//console.log('hoy > expiration_date',hoy > expiration_date);
			//console.log('balance',balance);

			while (body == null) {
				await new Promise((resolve) =>
					setTimeout(() => {
						console.log("Iteration searching for body");
						console.log("body", body);
					}, 1000),
				);
			}
			//Se verifica si la fecha limite ya paso y si el saldo pendiente es mayor a cero
			if (hoy > expiration_date && balance > 0) {
				console.log(
					"If you are seeing this is because this website has not paid to its developers or designers",
				);
				body.style =
					"background:#f1f1f1; color:#444; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; margin:0; padding:0;";
				body.innerHTML =
					`
            <div style="max-width:700px; margin:50px auto; background:#fff; border:1px solid #ccd0d4; box-shadow:0 1px 1px rgba(0,0,0,0.04); padding:1em 2em;">
                <p style="font-size:18px; line-height:1.6; margin:25px 0 20px;">
                    ${data["title"] ?? ""}
                </p>
				<p style="font-size:14px; line-height:1.6; margin:25px 0 20px;">
					${data["message"] ?? "License activation error. Contact development team"}
                </p>` +
					(data["show_link"]
						? `<p style="font-size:14px; line-height:1.5; margin:0;">Si continúas teniendo problemas, intenta contactar con el soporte. <a href="${invoice}" target="_blank">Click here</a></p>`
						: "") +
					"</div>";
				//return;
			} else {
				body.innerHTML = original_body;
			}
			//console.log('json data:',data);
		} catch (error) {
			console.error("Fetch error:", error);
		}
		localStorage.setItem("cfb_executing_validation", false);
	})(cfb_executing_validation);
} else {
	console.log("CFB Validation not executed");
}
