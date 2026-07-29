<?
use App\Doctors\DoctorService;
use App\Doctors\ProcedureService;
use Bitrix\Main\Localization\Loc;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
require __DIR__ . '/include.php';

Loc::loadMessages(__FILE__);

$doctorId = (int)($_GET['id'] ?? 0);
$isEditMode = ($_GET['edit'] ?? '') === 'Y';

$doctorService = new DoctorService();
$procedureService = new ProcedureService();

$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid() && ($_POST['action'] ?? '') === 'update_doctor')
{
    try
    {
        $doctorService->update($doctorId, $_POST, $_FILES['photo'] ?? null);
        LocalRedirect('/homeworks/homework3/doctor.php?id=' . $doctorId . '&success=updated');
    }
    catch (\Throwable $exception)
    {
        $errorMessage = getPageErrorMessage($exception);
        $isEditMode = true;
    }
}

if (($_GET['success'] ?? '') === 'updated')
{
    $successMessage = getPageSuccessMessage('DOCTOR_UPDATED_SUCCESS');
}

$data = $doctorService->getWithProcedures($doctorId);
$doctor = $data['doctor'];
$doctorProcedures = $data['procedures'];

$allProcedures = $procedureService->getList();
$specializations = $doctorService->getSpecializationOptions();
$specializationNames = [];

if ($doctor !== null)
{
    foreach ($doctor['SPECIALIZATION_IDS'] as $specId)
    {
        if (isset($specializations[$specId]))
        {
            $specializationNames[] = $specializations[$specId];
        }
    }
}

$pageTitle = $doctor !== null
    ? $doctor['FULL_NAME']
    : Loc::getMessage('DOCTOR_PAGE_TITLE');

$APPLICATION->SetTitle($pageTitle);
?>

    <section class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <h1 class="mb-0"><? $APPLICATION->ShowTitle(); ?></h1>
            <a href="/homeworks/homework3/" class="btn btn-outline-secondary">
                <?= Loc::getMessage('DOCTORS_BACK_TO_LIST'); ?>
            </a>
        </div>

        <? if ($successMessage !== ''): ?>
            <div class="alert alert-success"><?= $successMessage; ?></div>
        <? endif; ?>

        <? if ($errorMessage !== ''): ?>
            <div class="alert alert-danger"><?= $errorMessage; ?></div>
        <? endif; ?>

        <? if ($doctor === null): ?>
            <div class="alert alert-warning"><?= Loc::getMessage('DOCTOR_NOT_FOUND'); ?></div>
        <? elseif ($isEditMode): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <?= Loc::getMessage('DOCTOR_EDIT'); ?>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <?= bitrix_sessid_post(); ?>
                        <input type="hidden" name="action" value="update_doctor">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="last_name"><?= Loc::getMessage('FORM_LAST_NAME'); ?></label>
                                <input type="text" class="form-control" id="last_name" name="LAST_NAME"
                                       value="<?= htmlspecialcharsbx($doctor['LAST_NAME']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="first_name"><?= Loc::getMessage('FORM_FIRST_NAME'); ?></label>
                                <input type="text" class="form-control" id="first_name" name="FIRST_NAME"
                                       value="<?= htmlspecialcharsbx($doctor['FIRST_NAME']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="middle_name"><?= Loc::getMessage('FORM_MIDDLE_NAME'); ?></label>
                                <input type="text" class="form-control" id="middle_name" name="MIDDLE_NAME"
                                       value="<?= htmlspecialcharsbx($doctor['MIDDLE_NAME']); ?>">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label" for="photo"><?= Loc::getMessage('FORM_PHOTO'); ?></label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                            <? if (!empty($doctor['PREVIEW_PICTURE_SRC'])): ?>
                                <div class="mt-2">
                                    <img src="<?= htmlspecialcharsbx($doctor['PREVIEW_PICTURE_SRC']); ?>"
                                         alt="<?= htmlspecialcharsbx($doctor['FULL_NAME']); ?>"
                                         class="img-thumbnail"
                                         style="max-height: 180px;">
                                </div>
                            <? endif; ?>
                        </div>

                        <div class="mt-3">
                            <label class="form-label" for="proc_ids"><?= Loc::getMessage('FORM_PROCEDURES'); ?></label>
                            <select class="form-select" id="proc_ids" name="PROC_IDS[]" multiple size="6">
                                <? foreach ($allProcedures as $procedure): ?>
                                    <option value="<?= (int)$procedure['ID']; ?>"
                                        <?= in_array((int)$procedure['ID'], $doctor['PROCEDURE_IDS'], true) ? 'selected' : ''; ?>>
                                        <?= htmlspecialcharsbx($procedure['NAME']); ?>
                                    </option>
                                <? endforeach; ?>
                            </select>
                        </div>

                        <? if ($specializations !== []): ?>
                            <div class="mt-3">
                                <label class="form-label" for="spec_ids"><?= Loc::getMessage('FORM_SPECIALIZATION'); ?></label>
                                <select class="form-select" id="spec_ids" name="SPEC_IDS[]" multiple size="4">
                                    <? foreach ($specializations as $specId => $specName): ?>
                                        <option value="<?= (int)$specId; ?>"
                                            <?= in_array((int)$specId, $doctor['SPECIALIZATION_IDS'], true) ? 'selected' : ''; ?>>
                                            <?= htmlspecialcharsbx($specName); ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                        <? endif; ?>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary"><?= Loc::getMessage('FORM_SAVE'); ?></button>
                            <a href="/homeworks/homework3/doctor.php?id=<?= (int)$doctor['ID']; ?>"
                               class="btn btn-outline-secondary">
                                <?= Loc::getMessage('FORM_CANCEL'); ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <? else: ?>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <? if (!empty($doctor['PREVIEW_PICTURE_SRC'])): ?>
                            <img src="<?= htmlspecialcharsbx($doctor['PREVIEW_PICTURE_SRC']); ?>"
                                 class="card-img-top object-fit-cover"
                                 alt="<?= htmlspecialcharsbx($doctor['FULL_NAME']); ?>"
                                 style="height: 320px;">
                        <? endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialcharsbx($doctor['FULL_NAME']); ?></h5>
                            <p class="mb-1"><strong><?= Loc::getMessage('DOCTOR_SPECIALIZATION'); ?>:</strong></p>
                            <p class="text-muted">
                                <?= $specializationNames !== []
                                    ? htmlspecialcharsbx(implode(', ', $specializationNames))
                                    : Loc::getMessage('DOCTOR_SPECIALIZATION_EMPTY'); ?>
                            </p>
                            <a href="/homeworks/homework3/doctor.php?id=<?= (int)$doctor['ID']; ?>&edit=Y"
                               class="btn btn-primary">
                                <?= Loc::getMessage('DOCTOR_EDIT'); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-success text-white">
                            <?= Loc::getMessage('DOCTOR_PROCEDURES'); ?>
                        </div>
                        <ul class="list-group list-group-flush">
                            <? if ($doctorProcedures === []): ?>
                                <li class="list-group-item text-muted">
                                    <?= Loc::getMessage('DOCTOR_PROCEDURES_EMPTY'); ?>
                                </li>
                            <? else: ?>
                                <? foreach ($doctorProcedures as $procedure): ?>
                                    <li class="list-group-item">
                                        <?= htmlspecialcharsbx($procedure['NAME']); ?>
                                    </li>
                                <? endforeach; ?>
                            <? endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <? endif; ?>
    </section>

<? require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
