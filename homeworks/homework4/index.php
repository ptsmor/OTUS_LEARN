<?
use App\Clinic\Models\DoctorTable;
use App\Clinic\Models\ProcedureTable;
use App\Clinic\PatientVisitTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
require __DIR__ . '/include.php';

$APPLICATION->SetTitle('ДЗ #4: Создание своих таблиц БД и написание модели данных к ним');

$visits = [];
$errorMessage = '';

if (!Loader::includeModule('iblock'))
{
    $errorMessage = Loc::getMessage('VISITS_IBLOCK_REQUIRED');
}
else
{
    PatientVisitTable::ensureTableExists();

    if ((int)PatientVisitTable::getCount() === 0)
    {
        $doctor = DoctorTable::getList([
            'select' => ['ID'],
            'filter' => ['ACTIVE' => 'Y'],
            'limit' => 1,
        ])->fetch();

        $procedure = ProcedureTable::getList([
            'select' => ['ID'],
            'filter' => ['ACTIVE' => 'Y'],
            'limit' => 1,
        ])->fetch();

        if ($doctor && $procedure)
        {
            PatientVisitTable::add([
                'PATIENT_NAME' => 'Иванова Мария Петровна',
                'VISITS_COUNT' => 3,
                'DOCTOR_ID' => (int)$doctor['ID'],
                'PROCEDURE_ID' => (int)$procedure['ID'],
            ]);
        }
    }

    $collection = PatientVisitTable::getList([
        'select' => [
            'ID',
            'PATIENT_NAME',
            'VISITS_COUNT',
            'DOCTOR_ID',
            'PROCEDURE_ID',
            'DOCTOR',
            'PROCEDURE',
        ],
        'order' => ['ID' => 'ASC'],
        'cache' => [
            'ttl' => 3600,
            'cache_joins' => true,
        ],
    ])->fetchCollection();

    foreach ($collection as $visit)
    {
        $visits[] = mapPatientVisit($visit);
    }
}
?>

    <section class="container-fluid">
        <h1 class="mb-3"><? $APPLICATION->ShowTitle(); ?></h1>

        <h4 class="mb-3">Пояснительная записка</h4>
        <div class="mb-3">
            <p>Создана таблица <code>patient_visits</code> с полями разных типов:</p>
            <ul>
                <li><code>PATIENT_NAME</code> — строковое;</li>
                <li><code>VISITS_COUNT</code> — числовое;</li>
                <li><code>DOCTOR_ID</code>, <code>PROCEDURE_ID</code> — связываемые (числовой ID + <code>ReferenceField</code>).</li>
            </ul>
            <p>Для инфоблоков описаны модели <code>DoctorTable</code> и <code>ProcedureTable</code>
                (<code>local/App/Clinic/Models/</code>). Связь с таблицей <code>patient_visits</code> — по первичному ключу
                элемента инфоблока через поля <code>DOCTOR_ID</code> и <code>PROCEDURE_ID</code> + <code>ReferenceField</code>.</p>
            <p>Выборка через <code>fetchCollection()</code>: свойства инфоблока (ФИО врача) читаются геттерами
                связанного объекта <code>$visit->getDoctor()->getLastName()</code>. Кэш ORM с <code>cache_joins</code>.</p>
            <p>Таблица создаётся через ORM (<code>createDbTable()</code>).</p>
        </div>

        <hr class="my-4">

        <h4 class="mb-3">Приложение</h4>
        <p class="text-muted"><?= Loc::getMessage('VISITS_PAGE_DESCRIPTION'); ?></p>

        <? if ($errorMessage !== ''): ?>
            <div class="alert alert-danger"><?= htmlspecialcharsbx($errorMessage); ?></div>
        <? elseif ($visits === []): ?>
            <div class="alert alert-info"><?= Loc::getMessage('VISITS_LIST_EMPTY'); ?></div>
        <? else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col"><?= Loc::getMessage('VISITS_TABLE_PATIENT'); ?></th>
                        <th scope="col"><?= Loc::getMessage('VISITS_TABLE_VISITS'); ?></th>
                        <th scope="col"><?= Loc::getMessage('VISITS_TABLE_DOCTOR'); ?></th>
                        <th scope="col"><?= Loc::getMessage('VISITS_TABLE_PROCEDURE'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <? foreach ($visits as $visit): ?>
                        <tr>
                            <td><?= (int)$visit['ID']; ?></td>
                            <td><?= htmlspecialcharsbx($visit['PATIENT_NAME']); ?></td>
                            <td><?= (int)$visit['VISITS_COUNT']; ?></td>
                            <td><?= htmlspecialcharsbx($visit['DOCTOR_FULL_NAME']); ?></td>
                            <td><?= htmlspecialcharsbx($visit['PROCEDURE_NAME']); ?></td>
                        </tr>
                    <? endforeach; ?>
                    </tbody>
                </table>
            </div>
        <? endif; ?>

        <div class="card shadow-sm mt-5">
            <div class="card-header bg-success text-white">
                <?= Loc::getMessage('VISITS_FILES_TITLE'); ?>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item list-group-item-action">
                    <a href="/homeworks/homework4/"
                       class="d-flex justify-content-between align-items-center">
                        <span>homeworks/homework4/index.php</span>
                        <span class="badge bg-success">Список визитов</span>
                    </a>
                </li>
                <li class="list-group-item list-group-item-action">
                    <a href="/bitrix/admin/fileman_file_view.php?path=/local/App/Clinic/PatientVisitTable.php&lang=ru"
                       class="d-flex justify-content-between align-items-center">
                        <span>local/App/Clinic/PatientVisitTable.php</span>
                        <span class="badge bg-primary">Модель таблицы БД</span>
                    </a>
                </li>
                <li class="list-group-item list-group-item-action">
                    <a href="/bitrix/admin/fileman_file_view.php?path=/local/App/Clinic/Models/DoctorTable.php&lang=ru"
                       class="d-flex justify-content-between align-items-center">
                        <span>local/App/Clinic/Models/DoctorTable.php</span>
                        <span class="badge bg-warning">Модель инфоблока «Врачи»</span>
                    </a>
                </li>
                <li class="list-group-item list-group-item-action">
                    <a href="/bitrix/admin/fileman_file_view.php?path=/local/App/Clinic/Models/ProcedureTable.php&lang=ru"
                       class="d-flex justify-content-between align-items-center">
                        <span>local/App/Clinic/Models/ProcedureTable.php</span>
                        <span class="badge bg-warning">Модель инфоблока «Процедуры»</span>
                    </a>
                </li>
            </ul>
        </div>
    </section>

<? require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
