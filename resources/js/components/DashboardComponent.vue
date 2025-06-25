<template>
    <div class="container">
        <!-- Передаем данные в сводку как props -->
        <dashboard-summary :summary-data="summary" :loading="summaryLoading"></dashboard-summary>
        
        <hr class="mb-4">

        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary" @click="openClientModal()">Добавить клиента</button>
        </div>

        <div v-if="clientsLoading" class="text-center">Загрузка...</div>
        <div v-else-if="clients.length === 0" class="card"><div class="card-body text-center">У вас пока нет клиентов.</div></div>
        
        <!-- ... (остальной template без изменений) ... -->
        <div v-else class="accordion" id="clientsAccordion">
            <div v-for="client in clients" :key="client.id" class="accordion-item mb-2">
                <h2 class="accordion-header" :id="'heading' + client.id">
                     <button class="accordion-button collapsed" type="button" @click="toggleClient(client.id)">
                        <strong>{{ client.name }}</strong>
                        <div class="ms-auto pe-3">
                            <button class="btn btn-outline-primary btn-sm me-2" @click.stop="openClientModal(client)">Ред.</button>
                            <button class="btn btn-outline-danger btn-sm" @click.stop="handleDeleteClient(client.id)">Удл.</button>
                        </div>
                    </button>
                </h2>
                <div :id="'collapse' + client.id" class="accordion-collapse collapse" :aria-labelledby="'heading' + client.id" data-bs-parent="#clientsAccordion">
                    <div class="accordion-body">
                        <div class="d-flex justify-content-end mb-3 gap-2">
                            <button class="btn btn-outline-secondary btn-sm" @click="openManualTimeEntryModal(null, client.id)">Добавить запись вручную</button>
                            <button class="btn btn-success btn-sm" @click="openProjectModal(null, client.id)">Добавить проект</button>
                        </div>
                        <div v-if="!client.projects || client.projects.length === 0" class="list-group-item list-group-item-light text-center"><em>У этого клиента пока нет проектов.</em></div>
                        <div v-else class="accordion" :id="'projectsAccordion' + client.id">
                            <div v-for="project in client.projects" :key="project.id" class="accordion-item">
                                <h2 class="accordion-header" :id="'headingProject' + project.id"><button class="accordion-button collapsed" type="button" @click="toggleProject(project.id)">{{ project.title }}</button></h2>
                                <div :id="'collapseProject' + project.id" class="accordion-collapse collapse" :aria-labelledby="'headingProject' + project.id" :data-bs-parent="'#projectsAccordion' + client.id">
                                    <div class="accordion-body">
                                        <p>{{ project.description || 'Нет описания' }}</p><hr>
                                        <div class="time-tracker-controls mb-3">
                                            <div v-if="activeTimeEntry && activeTimeEntry.project_id === project.id"><div class="alert alert-primary d-flex justify-content-between align-items-center"><span>Работаем... <strong>{{ timerValue }}</strong></span><button class="btn btn-danger" @click="handleStopTimeTracking">⏹️ Остановить</button></div></div>
                                            <div v-else><div class="input-group"><input type="text" class="form-control" placeholder="Что делаем?" v-model="timeEntryDescriptions[project.id]"><button class="btn btn-primary" @click="handleStartTimeTracking(project.id)" :disabled="!!activeTimeEntry">▶️ Начать работу</button></div></div>
                                        </div>
                                        <h5>Записи времени:</h5>
                                        <ul v-if="timeEntries[project.id] && timeEntries[project.id].length > 0" class="list-group"><li v-for="entry in timeEntries[project.id]" :key="entry.id" class="list-group-item d-flex justify-content-between align-items-center"><span class="text-break">{{ entry.description || 'Без описания' }}</span><div class="d-flex align-items-center flex-shrink-0"><span class="badge bg-dark fw-normal rounded-pill me-3">{{ formatDuration(entry.duration) }}</span><div class="dropdown"><button class="btn btn-sm btn-light py-0 px-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">⋮</button><ul class="dropdown-menu"><li><a class="dropdown-item" href="#" @click.prevent="openTimeEntryModal(entry)">Редактировать</a></li><li><a class="dropdown-item text-danger" href="#" @click.prevent="handleDeleteTimeEntry(entry.id, project.id)">Удалить</a></li></ul></div></div></li></ul>
                                        <div v-else class="text-muted text-center p-2">Пока нет записей.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальные окна -->
        <div class="modal fade" id="clientModal" tabindex="-1" ref="clientModal"><div class="modal-dialog"><div class="modal-content"><form @submit.prevent="handleSubmitClient"><div class="modal-header"><h5 class="modal-title">{{ isEditMode ? 'Редактировать клиента' : 'Новый клиент' }}</h5><button type="button" class="btn-close" @click="closeClientModal"></button></div><div class="modal-body"><div class="mb-3"><label class="form-label">Имя</label><input type="text" class="form-control" v-model="clientForm.name" required></div><div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" v-model="clientForm.email"></div><div class="mb-3"><label class="form-label">Телефон</label><input type="text" class="form-control" v-model="clientForm.phone"></div><div class="mb-3"><label class="form-label">Ставка по умолчанию (RUB/час)</label><input type="number" class="form-control" v-model.number="clientForm.default_rate" step="0.01" placeholder="Не обязательно"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" @click="closeClientModal">Отмена</button><button type="submit" class="btn btn-primary">Сохранить</button></div></form></div></div></div>
        <div class="modal fade" id="projectModal" tabindex="-1" ref="projectModal"><div class="modal-dialog"><div class="modal-content"><form @submit.prevent="handleSubmitProject"><div class="modal-header"><h5 class="modal-title">{{ isEditMode ? 'Редактировать проект' : 'Новый проект' }}</h5><button type="button" class="btn-close" @click="closeProjectModal"></button></div><div class="modal-body"><div class="mb-3"><label class="form-label">Название</label><input type="text" class="form-control" v-model="projectForm.title" required></div><div class="mb-3"><label class="form-label">Описание</label><textarea class="form-control" v-model="projectForm.description" rows="3"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" @click="closeProjectModal">Отмена</button><button type="submit" class="btn btn-primary">Сохранить</button></div></form></div></div></div>
        <div class="modal fade" id="timeEntryModal" tabindex="-1" ref="timeEntryModal"><div class="modal-dialog"><div class="modal-content"><form @submit.prevent="handleSubmitTimeEntry"><div class="modal-header"><h5 class="modal-title">Редактировать запись времени</h5><button type="button" class="btn-close" @click="closeTimeEntryModal"></button></div><div class="modal-body"><div class="mb-3"><label class="form-label">Описание</label><input type="text" class="form-control" v-model="timeEntryForm.description"></div><div class="row"><div class="col-md-6 mb-3"><label class="form-label">Время начала</label><input type="datetime-local" class="form-control" v-model="timeEntryForm.start_time"></div><div class="col-md-6 mb-3"><label class="form-label">Время окончания</label><input type="datetime-local" class="form-control" v-model="timeEntryForm.end_time"></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" @click="closeTimeEntryModal">Отмена</button><button type="submit" class="btn btn-primary">Сохранить</button></div></form></div></div></div>
        <div class="modal fade" id="manualTimeEntryModal" tabindex="-1" ref="manualTimeEntryModal"><div class="modal-dialog"><div class="modal-content"><form @submit.prevent="handleStoreManualTimeEntry"><div class="modal-header"><h5 class="modal-title">Добавить запись времени</h5><button type="button" class="btn-close" @click="closeManualTimeEntryModal"></button></div><div class="modal-body"><div class="mb-3"><label class="form-label">Проект</label><select class="form-select" v-model="manualTimeEntryForm.project_id" required><option disabled value="">Выберите проект...</option><option v-for="project in allProjects" :key="project.id" :value="project.id">{{ project.title }} ({{ project.client.name }})</option></select></div><div class="mb-3"><label class="form-label">Описание</label><input type="text" class="form-control" v-model="manualTimeEntryForm.description"></div><div class="row"><div class="col-md-6 mb-3"><label class="form-label">Время начала</label><input type="datetime-local" class="form-control" v-model="manualTimeEntryForm.start_time" required></div><div class="col-md-6 mb-3"><label class="form-label">Время окончания</label><input type="datetime-local" class="form-control" v-model="manualTimeEntryForm.end_time" required></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" @click="closeManualTimeEntryModal">Отмена</button><button type="submit" class="btn btn-primary">Сохранить</button></div></form></div></div></div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';
import { Modal, Collapse } from 'bootstrap';

// --- Состояния компонента ---
const summary = reactive({ totalUnpaid: 0, incomeThisMonth: 0, unbilledHours: 0, activeTimer: null });
const summaryLoading = ref(true);
const clients = ref([]);
const clientsLoading = ref(true);
const isEditMode = ref(false);
const timeEntries = reactive({});
const timeEntryDescriptions = reactive({});
const activeTimeEntry = ref(null);
const timerValue = ref('00:00:00');
let timerInterval = null;
let clientModalInstance, projectModalInstance, timeEntryModalInstance, manualTimeEntryModalInstance;
const clientModal = ref(null);
const projectModal = ref(null);
const timeEntryModal = ref(null);
const manualTimeEntryModal = ref(null);
const collapseInstances = {};

const clientForm = reactive({ id: null, name: '', email: '', phone: '', default_rate: null });
const projectForm = reactive({ id: null, client_id: null, title: '', description: '' });
const timeEntryForm = reactive({ id: null, project_id: null, description: '', start_time: '', end_time: '' });
const manualTimeEntryForm = reactive({ project_id: null, description: '', start_time: '', end_time: ''});

const allProjects = computed(() => clients.value.flatMap(client => client.projects.map(p => ({...p, client: { name: client.name }}))));

// --- Методы загрузки данных ---
const fetchSummary = async () => { summaryLoading.value = true; try { const response = await axios.get('/api/dashboard-summary'); Object.assign(summary, response.data); } catch (error) { console.error("Не удалось загрузить сводку:", error); } finally { summaryLoading.value = false; } };
const fetchClients = async () => { clientsLoading.value = true; try { const response = await axios.get('/api/clients'); clients.value = response.data; } catch (error) { handleError(error, 'клиентов'); } finally { clientsLoading.value = false; } };

// --- Методы для Клиентов ---
const openClientModal = (client = null) => { isEditMode.value = !!client; Object.assign(clientForm, client || { id: null, name: '', email: '', phone: '', default_rate: null }); clientModalInstance?.show(); };
const closeClientModal = () => clientModalInstance?.hide();
const handleSubmitClient = async () => { try { if (isEditMode.value) { await axios.put(`/api/clients/${clientForm.id}`, clientForm); } else { await axios.post('/api/clients', clientForm); } closeClientModal(); await fetchClients(); } catch (error) { handleError(error, 'клиента'); } };
const handleDeleteClient = async (clientId) => { if (confirm('Удалить клиента и все его проекты?')) { try { await axios.delete(`/api/clients/${clientId}`); await fetchClients(); fetchSummary(); } catch (error) { handleError(error, 'клиента'); } } };

// --- Методы для Проектов ---
const openProjectModal = (project = null, clientId = null) => { isEditMode.value = !!project; Object.assign(projectForm, project || { id: null, client_id: clientId, title: '', description: '' }); projectModalInstance?.show(); };
const closeProjectModal = () => projectModalInstance?.hide();
const handleSubmitProject = async () => { try { if (isEditMode.value) { await axios.put(`/api/projects/${projectForm.id}`, projectForm); } else { await axios.post('/api/projects', projectForm); } closeProjectModal(); await fetchClients(); } catch (error) { handleError(error, 'проекта'); } };

// --- Методы для Записей Времени ---
const fetchTimeEntries = async (projectId) => { try { const response = await axios.get(`/api/projects/${projectId}/time-entries`); timeEntries[projectId] = response.data; } catch (error) { handleError(error, `записей времени`); } };
const handleStartTimeTracking = async (projectId) => { try { const response = await axios.post(`/api/projects/${projectId}/time-entries/start`, { description: timeEntryDescriptions[projectId] || null, }); activeTimeEntry.value = response.data; startTimerDisplay(response.data.start_time); fetchSummary(); } catch (error) { handleError(error, 'запуска таймера'); } };
const handleStopTimeTracking = async () => { if (!activeTimeEntry.value) return; try { const response = await axios.patch(`/api/time-entries/${activeTimeEntry.value.id}/stop`); const stoppedEntry = response.data; const projectEntries = timeEntries[stoppedEntry.project_id]; if (projectEntries) { const index = projectEntries.findIndex(e => e.id === stoppedEntry.id); if (index !== -1) { projectEntries[index] = stoppedEntry; } else { projectEntries.unshift(stoppedEntry); } } activeTimeEntry.value = null; stopTimerDisplay(); fetchSummary(); } catch (error) { handleError(error, 'остановки таймера'); } };
const openTimeEntryModal = (entry) => { Object.assign(timeEntryForm, { ...entry, start_time: formatDatetimeForInput(entry.start_time), end_time: formatDatetimeForInput(entry.end_time) }); timeEntryModalInstance?.show(); };
const closeTimeEntryModal = () => timeEntryModalInstance?.hide();
const handleSubmitTimeEntry = async () => { try { const response = await axios.put(`/api/time-entries/${timeEntryForm.id}`, timeEntryForm); const updatedEntry = response.data; const projectEntries = timeEntries[updatedEntry.project_id]; if(projectEntries) { const index = projectEntries.findIndex(e => e.id === updatedEntry.id); if (index !== -1) projectEntries[index] = updatedEntry; } closeTimeEntryModal(); fetchSummary(); } catch (error) { handleError(error, 'обновления записи'); } };
const handleDeleteTimeEntry = async (entryId, projectId) => { if (confirm('Удалить эту запись времени?')) { try { await axios.delete(`/api/time-entries/${entryId}`); await fetchTimeEntries(projectId); fetchSummary(); } catch (error) { handleError(error, 'удаления записи'); } } };

// --- Методы для ручного ввода ---
const openManualTimeEntryModal = (projectId = null, clientId = null) => { let initialProjectId = projectId; if (!initialProjectId && clientId) { const client = clients.value.find(c => c.id === clientId); if (client?.projects?.length > 0) { initialProjectId = client.projects[0].id; } } Object.assign(manualTimeEntryForm, { project_id: initialProjectId, description: '', start_time: '', end_time: ''}); manualTimeEntryModalInstance?.show(); };
const closeManualTimeEntryModal = () => manualTimeEntryModalInstance?.hide();
const handleStoreManualTimeEntry = async () => { const { project_id, ...payload } = manualTimeEntryForm; if (!project_id) { alert('Выберите проект!'); return; } try { await axios.post(`/api/projects/${project_id}/time-entries/manual`, payload); closeManualTimeEntryModal(); if (timeEntries[project_id]) { await fetchTimeEntries(project_id); } fetchSummary(); } catch (error) { handleError(error, 'добавления записи'); } };

// --- Общие и служебные методы ---
const checkForActiveTimer = async () => { try { const response = await axios.get('/api/time-entries/active'); if (response.data) { activeTimeEntry.value = response.data; startTimerDisplay(response.data.start_time); } } catch (error) { console.error("Ошибка проверки таймера:", error); } };
const startTimerDisplay = (startTime) => { stopTimerDisplay(); const start = new Date(startTime); timerInterval = setInterval(() => { const now = new Date(); const diff = Math.floor((now - start) / 1000); timerValue.value = formatDuration(diff); }, 1000); };
const stopTimerDisplay = () => { clearInterval(timerInterval); timerInterval = null; timerValue.value = '00:00:00'; };
const formatDuration = (totalSeconds) => { if (totalSeconds === null || typeof totalSeconds === 'undefined' || totalSeconds < 0) return '00:00:00'; const h = Math.floor(totalSeconds / 3600).toString().padStart(2, '0'); const m = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0'); const s = (totalSeconds % 60).toString().padStart(2, '0'); return `${h}:${m}:${s}`; };
const formatDatetimeForInput = (dateTimeString) => { if (!dateTimeString) return ''; return new Date(dateTimeString).toISOString().slice(0, 16); };
const toggleClient = (clientId) => { const el = document.getElementById(`collapse${clientId}`); if (el) { if (!collapseInstances[clientId]) { collapseInstances[clientId] = new Collapse(el, { toggle: false }); } collapseInstances[clientId].toggle(); }};
const toggleProject = async (projectId) => { const el = document.getElementById(`collapseProject${projectId}`); const key = `project_${projectId}`; if (el) { if (!collapseInstances[key]) { collapseInstances[key] = new Collapse(el, { toggle: false }); } collapseInstances[key].toggle(); } if (!timeEntries[projectId]) { await fetchTimeEntries(projectId); }};
const handleError = (error, entityName) => { if (error.response?.status === 422) { const errors = error.response.data.errors; let errorMessage = "Ошибка валидации:\n" + Object.values(errors).map(e => `- ${e[0]}`).join("\n"); alert(errorMessage); } else if (error.response?.data?.message) { alert(`Ошибка: ${error.response.data.message}`); } else { console.error(`Ошибка при операции с ${entityName}:`, error); alert(`Произошла ошибка при операции с ${entityName}.`); } };

// --- Хуки жизненного цикла ---
onMounted(async () => {
    clientsLoading.value = true;
    clientModalInstance = clientModal.value ? new Modal(clientModal.value) : null;
    projectModalInstance = projectModal.value ? new Modal(projectModal.value) : null;
    timeEntryModalInstance = timeEntryModal.value ? new Modal(timeEntryModal.value) : null;
    manualTimeEntryModalInstance = manualTimeEntryModal.value ? new Modal(manualTimeEntryModal.value) : null;
    
    await Promise.all([fetchClients(), fetchSummary()]);
    
    await checkForActiveTimer();
    clientsLoading.value = false;

    [clientModal, projectModal, timeEntryModal, manualTimeEntryModal].forEach(modalRef => {
        modalRef.value?.addEventListener('hidden.bs.modal', () => {
            const focusedElement = document.querySelector(':focus');
            if (focusedElement && modalRef.value?.contains(focusedElement)) {
                focusedElement.blur();
            }
        });
    });
});

onUnmounted(() => {
    stopTimerDisplay();
    clientModalInstance?.dispose();
    projectModalInstance?.dispose();
    timeEntryModalInstance?.dispose();
    manualTimeEntryModalInstance?.dispose();
});
</script>