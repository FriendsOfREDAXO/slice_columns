function createSection(button) {
    var article_id = $(button).data('article-id');
    var clang_id = $(button).data('clang-id');
    var slice_id = $(button).data('slice-id');
    
    var section_name = prompt("Bitte geben Sie einen Namen für den neuen Abschnitt ein:", "");
    if (section_name !== null) {
        $.post(
            "index.php?page=content/edit&rex-api-call=sections",
            {
                function: "create_section",
                article_id: article_id,
                clang_id: clang_id,
                slice_id: slice_id,
                section_name: section_name
            },
            function(result) {
                if (result.success) {
                    // Seite neu laden, um die Änderungen zu sehen
                    location.reload();
                } else {
                    alert("Fehler beim Erstellen des Abschnitts!");
                }
            },
            'json'
        );
    }
}

function addToSection(button) {
    var section_id = $(button).data('section-id');
    var slice_id = $(button).data('slice-id');
    
    $.post(
        "index.php?page=content/edit&rex-api-call=sections",
        {
            function: "add_to_section",
            section_id: section_id,
            slice_id: slice_id
        },
        function(result) {
            if (result.success) {
                location.reload();
            } else {
                alert("Fehler beim Hinzufügen zum Abschnitt!");
            }
        },
        'json'
    );
}

function removeFromSection(button) {
    var slice_id = $(button).data('slice-id');
    
    if (confirm("Möchten Sie diesen Block wirklich aus dem Abschnitt entfernen?")) {
        $.post(
            "index.php?page=content/edit&rex-api-call=sections",
            {
                function: "remove_from_section",
                slice_id: slice_id
            },
            function(result) {
                if (result.success) {
                    location.reload();
                } else {
                    alert("Fehler beim Entfernen aus dem Abschnitt!");
                }
            },
            'json'
        );
    }
}
