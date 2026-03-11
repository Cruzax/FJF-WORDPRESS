(function () {
    "use strict";

    function initPlanningDemo() {
        var buttons = document.querySelectorAll(".planning-table .slot-action-btn");
        if (!buttons.length) {
            return;
        }

        buttons.forEach(function (button, index) {
            // Si deja indisponible dans le HTML, on ne touche pas.
            if (button.classList.contains("slot-action-btn--unavailable") || button.disabled) {
                return;
            }

            // Demo: on rend 1 creneau sur 4 indisponible pour visualiser l'etat.
            if (index % 4 === 0) {
                button.classList.add("slot-action-btn--unavailable");
                button.disabled = true;
                button.textContent = "Indisponible";
                return;
            }

            // Creneau cliquable: on met en evidence la selection.
            button.addEventListener("click", function () {
                if (button.disabled) {
                    return;
                }

                var table = button.closest(".planning-table");
                if (!table) {
                    return;
                }

                var selected = table.querySelectorAll(".slot-action-btn--selected");
                selected.forEach(function (item) {
                    item.classList.remove("slot-action-btn--selected");
                    item.textContent = "Disponible";
                });

                button.classList.add("slot-action-btn--selected");
                button.textContent = "Selectionne";
            });
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initPlanningDemo);
    } else {
        initPlanningDemo();
    }
})();
