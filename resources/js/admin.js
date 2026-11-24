import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import 'admin-lte/dist/js/adminlte.js';
import Chart from 'chart.js/auto';

const chartCanvas = document.getElementById('memoires-par-filiere');
const chartDataElement = document.getElementById('memoires-par-filiere-data');

if (chartCanvas instanceof HTMLCanvasElement && chartDataElement instanceof HTMLScriptElement) {
    const chartData = JSON.parse(chartDataElement.textContent ?? '{}');

    new Chart(chartCanvas, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Mémoires',
                data: chartData.values,
                backgroundColor: '#0d6efd',
                borderRadius: 4,
                maxBarThickness: 36,
            }],
        },
        options: {
            indexAxis: 'y',
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        stepSize: 1,
                    },
                },
                y: {
                    grid: {
                        display: false,
                    },
                },
            },
            plugins: {
                legend: {
                    display: false,
                },
            },
        },
    });
}

const userRoleSelect = document.querySelector('[data-user-role]');

if (userRoleSelect instanceof HTMLSelectElement) {
    const roleDependentFields = document.querySelectorAll('[data-required-roles]');

    const updateRequiredFields = () => {
        roleDependentFields.forEach((field) => {
            const requiredRoles = field.dataset.requiredRoles.split(',');
            field.required = requiredRoles.includes(userRoleSelect.value);
        });
    };

    userRoleSelect.addEventListener('change', updateRequiredFields);
    updateRequiredFields();
}

const authorUserSelect = document.querySelector('[data-author-user]');

if (authorUserSelect instanceof HTMLSelectElement) {
    const authorFields = document.querySelector('[data-author-fields]');
    const authorHelp = document.querySelector('[data-author-help]');
    const authorInputs = document.querySelectorAll('[data-required-without-user]');

    const updateAuthorFields = () => {
        const hasLinkedStudent = authorUserSelect.value !== '';

        if (authorFields instanceof HTMLElement) {
            authorFields.hidden = hasLinkedStudent;
        }
        if (authorHelp instanceof HTMLElement) {
            authorHelp.hidden = hasLinkedStudent;
        }

        authorInputs.forEach((field) => {
            field.required = !hasLinkedStudent;
        });
    };

    authorUserSelect.addEventListener('change', updateAuthorFields);
    updateAuthorFields();
}

const archiveFileInput = document.querySelector('[data-archive-file]');
const archiveDropzone = document.querySelector('[data-archive-dropzone]');
const archiveFileName = document.querySelector('[data-archive-file-name]');

if (
    archiveFileInput instanceof HTMLInputElement
    && archiveDropzone instanceof HTMLElement
    && archiveFileName instanceof HTMLElement
) {
    const updateArchiveFileName = () => {
        const selectedFile = archiveFileInput.files?.[0];
        archiveFileName.textContent = selectedFile?.name ?? 'Glissez votre PDF ici';
    };

    archiveFileInput.addEventListener('change', updateArchiveFileName);

    archiveDropzone.addEventListener('dragover', (event) => {
        event.preventDefault();
        archiveDropzone.classList.add('is-dragover');
    });

    archiveDropzone.addEventListener('dragleave', (event) => {
        if (event.relatedTarget instanceof Node && archiveDropzone.contains(event.relatedTarget)) {
            return;
        }

        archiveDropzone.classList.remove('is-dragover');
    });

    archiveDropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        archiveDropzone.classList.remove('is-dragover');

        const droppedFiles = event.dataTransfer?.files;
        if (droppedFiles && droppedFiles.length > 0) {
            archiveFileInput.files = droppedFiles;
            archiveFileInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
}
