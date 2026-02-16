document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("#registerForm");
  if (!form) return;

  const statusBox = document.querySelector("#formStatus");

  const map = {
    nom: { input: "#nom", err: "#nomError" },
    prenom: { input: "#prenom", err: "#prenomError" },
    email: { input: "#email", err: "#emailError" },
    password: { input: "#password", err: "#passwordError" },
    confirm_password: { input: "#confirm_password", err: "#confirmPasswordError" },
    telephone: { input: "#telephone", err: "#telephoneError" },
  };

  function setStatus(type, msg) {
    if (!statusBox) return;
    if (!msg) {
      statusBox.className = "alert d-none";
      statusBox.textContent = "";
      return;
    }
    statusBox.className = `alert alert-${type}`;
    statusBox.textContent = msg;
  }

  function clearFeedback() {
    Object.keys(map).forEach((k) => {
      const input = document.querySelector(map[k].input);
      const err = document.querySelector(map[k].err);
      input.classList.remove("is-invalid", "is-valid");
      if (err) err.textContent = "";
    });
    setStatus(null, "");
  }

  function applyServerResult(data) {
    // 1) Appliquer valeurs normalisées (ex: téléphone sans espaces)
    if (data.values?.telephone) {
      const tel = document.querySelector("#telephone");
      tel.value = data.values.telephone;
    }

    // 2) Appliquer erreurs / états
    Object.keys(map).forEach((k) => {
      const input = document.querySelector(map[k].input);
      const err = document.querySelector(map[k].err);
      const msg = data.errors?.[k] || "";

      if (msg) {
        input.classList.add("is-invalid");
        input.classList.remove("is-valid");
        if (err) err.textContent = msg;
      } else {
        input.classList.remove("is-invalid");
        input.classList.add("is-valid");
        if (err) err.textContent = "";
      }
    });

    // 3) Message global si besoin
    if (data.errors?._global) {
      setStatus("warning", data.errors._global);
    }
  }

  async function callValidate() {
    const fd = new FormData(form);
    const res = await fetch("validate.php", {
      method: "POST",
      body: fd,
      headers: { "X-Requested-With": "XMLHttpRequest" },
    });

    if (!res.ok) throw new Error("Erreur serveur lors de la validation.");
    return res.json();
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    clearFeedback();

    try {
      const data = await callValidate();
      applyServerResult(data);

      if (data.ok) {
        setStatus("success", "Validation OK ✅ Envoi en cours...");
        form.submit(); // soumission finale vers register.php (qui revalide)
      } else {
        setStatus("danger", "Veuillez corriger les erreurs.");
      }
    } catch (err) {
      setStatus("warning", err.message || "Une erreur est survenue.");
    }
  });

  // Optionnel : validation au blur (moins de spam que input)
  Object.keys(map).forEach((k) => {
    const input = document.querySelector(map[k].input);
    input.addEventListener("blur", async () => {
      try {
        const data = await callValidate();
        applyServerResult(data);
      } catch (_) {}
    });
  });
});