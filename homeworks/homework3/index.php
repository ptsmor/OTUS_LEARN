<?
use App\Doctors\DoctorService;
use App\Doctors\ProcedureService;
use Bitrix\Main\Localization\Loc;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
require __DIR__ . '/include.php';

$APPLICATION->SetTitle('ДЗ #3: Связывание моделей');

$doctorService = new DoctorService();
$procedureService = new ProcedureService();

$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid())
{
    try
    {
        if (($_POST['action'] ?? '') === 'add_doctor')
        {
            $doctorService->create($_POST, $_FILES['photo'] ?? null);
            LocalRedirect('/homeworks/homework3/?success=doctor_created');
        }

        if (($_POST['action'] ?? '') === 'add_procedure')
        {
            $procedureService->create((string)($_POST['PROCEDURE_NAME'] ?? ''));
            LocalRedirect('/homeworks/homework3/?success=procedure_created');
        }
    }
    catch (\Throwable $exception)
    {
        $errorMessage = getPageErrorMessage($exception);
    }
}

if (($_GET['success'] ?? '') === 'doctor_created')
{
    $successMessage = getPageSuccessMessage('DOCTOR_CREATED_SUCCESS');
}

if (($_GET['success'] ?? '') === 'procedure_created')
{
    $successMessage = getPageSuccessMessage('PROCEDURE_CREATED_SUCCESS');
}

$doctors = $doctorService->getList();
$procedures = $procedureService->getList();
$specializations = $doctorService->getSpecializationOptions();
?>

    <section class="container-fluid">
        <h1 class="mb-3"><? $APPLICATION->ShowTitle(); ?></h1>

        <h4 class="mb-3">Пояснительная записка</h4>
        <div class="mb-3">
            <p>Созданы инфоблоки <strong>«Врачи»</strong> и <strong>«Процедуры»</strong>, процедуры привязаны к врачам
                через свойство <code>PROC_IDS</code>.</p>
            <p>На странице <code>/homeworks/homework3/</code> выводится список врачей, по клику открывается карточка
                с данными и процедурами. Реализовано добавление врача, процедуры и редактирование врача.</p>
            <p>Использованы D7 ORM (<code>ElementDoctorsTable</code>, <code>ElementProceduresTable</code>) для чтения
                и абстрактный класс <code>App\Doctors\Abstract\AbstractIblockService</code> для сохранения через
                <code>CIBlockElement</code>.</p>
        </div>

        <hr class="my-4">

        <h4 class="mb-3">Приложение</h4>
        <p class="text-muted"><?= Loc::getMessage('DOCTORS_DESCRIPTION'); ?></p>

        <? if ($successMessage !== ''): ?>
            <div class="alert alert-success"><?= $successMessage; ?></div>
        <? endif; ?>

        <? if ($errorMessage !== ''): ?>
            <div class="alert alert-danger"><?= $errorMessage; ?></div>
        <? endif; ?>

        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="#add-doctor-form" class="btn btn-primary"><?= Loc::getMessage('DOCTORS_ADD_DOCTOR'); ?></a>
            <a href="#add-procedure-form" class="btn btn-outline-primary"><?= Loc::getMessage('DOCTORS_ADD_PROCEDURE'); ?></a>
        </div>

        <div class="row g-4">
            <? if ($doctors === []): ?>
                <div class="col-12">
                    <div class="alert alert-info mb-0"><?= Loc::getMessage('DOCTORS_LIST_EMPTY'); ?></div>
                </div>
            <? else: ?>
                <? foreach ($doctors as $doctor): ?>
                    <div class="col-md-4">
                        <div class="card shadow-sm h-100">
                            <? if (!empty($doctor['PREVIEW_PICTURE_SRC'])): ?>
                                <img src="<?= htmlspecialcharsbx($doctor['PREVIEW_PICTURE_SRC']); ?>"
                                     class="card-img-top object-fit-cover"
                                     alt="<?= htmlspecialcharsbx($doctor['FULL_NAME']); ?>"
                                     style="height: 220px;">
                            <? endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialcharsbx($doctor['FULL_NAME']); ?></h5>
                                <p class="card-text text-muted mb-3">
                                    <?= Loc::getMessage(
                                        'DOCTOR_PROCEDURES_COUNT',
                                        ['#COUNT#' => count($doctor['PROCEDURE_IDS'])]
                                    ); ?>
                                </p>
                                <a href="/homeworks/homework3/doctor.php?id=<?= (int)$doctor['ID']; ?>"
                                   class="btn btn-success mt-auto">
                                    <?= Loc::getMessage('DOCTOR_OPEN_CARD'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <? endforeach; ?>
            <? endif; ?>
        </div>

        <hr class="my-5">

        <div class="row g-4">
            <div class="col-lg-6" id="add-doctor-form">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <?= Loc::getMessage('DOCTORS_ADD_DOCTOR'); ?>
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <?= bitrix_sessid_post(); ?>
                            <input type="hidden" name="action" value="add_doctor">

                            <div class="mb-3">
                                <label class="form-label" for="last_name"><?= Loc::getMessage('FORM_LAST_NAME'); ?></label>
                                <input type="text" class="form-control" id="last_name" name="LAST_NAME" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="first_name"><?= Loc::getMessage('FORM_FIRST_NAME'); ?></label>
                                <input type="text" class="form-control" id="first_name" name="FIRST_NAME" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="middle_name"><?= Loc::getMessage('FORM_MIDDLE_NAME'); ?></label>
                                <input type="text" class="form-control" id="middle_name" name="MIDDLE_NAME">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="photo"><?= Loc::getMessage('FORM_PHOTO'); ?></label>
                                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="proc_ids"><?= Loc::getMessage('FORM_PROCEDURES'); ?></label>
                                <select class="form-select" id="proc_ids" name="PROC_IDS[]" multiple size="5">
                                    <? foreach ($procedures as $procedure): ?>
                                        <option value="<?= (int)$procedure['ID']; ?>">
                                            <?= htmlspecialcharsbx($procedure['NAME']); ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>

                            <? if ($specializations !== []): ?>
                                <div class="mb-3">
                                    <label class="form-label" for="spec_ids"><?= Loc::getMessage('FORM_SPECIALIZATION'); ?></label>
                                    <select class="form-select" id="spec_ids" name="SPEC_IDS[]" multiple size="4">
                                        <? foreach ($specializations as $specId => $specName): ?>
                                            <option value="<?= (int)$specId; ?>">
                                                <?= htmlspecialcharsbx($specName); ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                            <? endif; ?>

                            <button type="submit" class="btn btn-primary"><?= Loc::getMessage('FORM_SAVE'); ?></button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6" id="add-procedure-form">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <?= Loc::getMessage('DOCTORS_ADD_PROCEDURE'); ?>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <?= bitrix_sessid_post(); ?>
                            <input type="hidden" name="action" value="add_procedure">

                            <div class="mb-3">
                                <label class="form-label" for="procedure_name"><?= Loc::getMessage('FORM_PROCEDURE_NAME'); ?></label>
                                <input type="text" class="form-control" id="procedure_name" name="PROCEDURE_NAME" required>
                            </div>

                            <button type="submit" class="btn btn-outline-primary"><?= Loc::getMessage('FORM_SAVE'); ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-5">
            <div class="card-header bg-success text-white">
                Файлы проекта
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item list-group-item-action">
                    <a href="/homeworks/homework3/"
                       class="d-flex justify-content-between align-items-center">
                        <span>homeworks/homework3/index.php</span>
                        <span class="badge bg-success">Список врачей</span>
                    </a>
                </li>
                <li class="list-group-item list-group-item-action">
                    <a href="/homeworks/homework3/doctor.php"
                       class="d-flex justify-content-between align-items-center">
                        <span>homeworks/homework3/doctor.php</span>
                        <span class="badge bg-secondary">Карточка врача</span>
                    </a>
                </li>
                <li class="list-group-item list-group-item-action">
                    <a href="/bitrix/admin/fileman_file_view.php?path=/local/App/Doctors/Abstract/AbstractIblockService.php&lang=ru"
                       class="d-flex justify-content-between align-items-center">
                        <span>local/App/Doctors/Abstract/AbstractIblockService.php</span>
                        <span class="badge bg-primary">Абстрактный класс</span>
                    </a>
                </li>
                <li class="list-group-item list-group-item-action">
                    <a href="/bitrix/admin/fileman_file_view.php?path=/local/App/Doctors/DoctorService.php&lang=ru"
                       class="d-flex justify-content-between align-items-center">
                        <span>local/App/Doctors/DoctorService.php</span>
                        <span class="badge bg-warning">Сервис врачей</span>
                    </a>
                </li>
                <li class="list-group-item list-group-item-action">
                    <a href="/bitrix/admin/fileman_file_view.php?path=/local/App/Doctors/ProcedureService.php&lang=ru"
                       class="d-flex justify-content-between align-items-center">
                        <span>local/App/Doctors/ProcedureService.php</span>
                        <span class="badge bg-warning">Сервис процедур</span>
                    </a>
                </li>
            </ul>
        </div>
    </section>

<? require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
