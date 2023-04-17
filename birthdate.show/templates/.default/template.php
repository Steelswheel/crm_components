<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loader::IncludeModule('crm');
$this->addExternalCss('/style.css');
global $USER;

$user_id = CUser::GetByID($USER->GetID())->Fetch()['ID'];
?>

<div class="birthdate-show">
    <div class="birthdate-show-wrap">
        <div class="birthdate-show-icon">
            <svg width="32px" height="32px" viewBox="0 0 32 32" data-name="Layer 2" id="Layer_2" xmlns="http://www.w3.org/2000/svg">
                <title/>
                <path d="M24.16,14.21,24.1,14,24,13.88l-.12-.14a.9.9,0,0,0-1.3,0l-.12.14-.08.16a1.26,1.26,0,0,0,0,.18.6.6,0,0,0,0,.18.91.91,0,0,0,.06.35.94.94,0,0,0,.2.3.81.81,0,0,0,.3.2,1,1,0,0,0,.36.07l.18,0,.18-.06.16-.08A.59.59,0,0,0,23.9,15a.81.81,0,0,0,.2-.3.91.91,0,0,0,.07-.35A1.22,1.22,0,0,0,24.16,14.21Z"/>
                <path d="M26,10.52l-.06-.18-.08-.16L25.74,10a.9.9,0,0,0-1.3,0l-.12.14-.08.16a1.26,1.26,0,0,0,0,.18.6.6,0,0,0,0,.18.91.91,0,0,0,.06.35.94.94,0,0,0,.2.3.81.81,0,0,0,.3.2,1,1,0,0,0,.36.07l.18,0,.18-.06.16-.08a.59.59,0,0,0,.14-.12.81.81,0,0,0,.2-.3A.91.91,0,0,0,26,10.7,1.22,1.22,0,0,0,26,10.52Z"/>
                <path d="M21.39,5.9l-.06-.18-.08-.16-.12-.14a.9.9,0,0,0-1.3,0l-.12.14-.08.16a1.26,1.26,0,0,0,0,.18.6.6,0,0,0,0,.18.91.91,0,0,0,.06.35.94.94,0,0,0,.2.3.81.81,0,0,0,.3.2,1,1,0,0,0,.36.07l.18,0,.18-.06L21,6.85a.59.59,0,0,0,.14-.12.81.81,0,0,0,.2-.3.91.91,0,0,0,.07-.35A1.22,1.22,0,0,0,21.39,5.9Z"/>
                <path d="M17.69,9.59l-.06-.18-.08-.16-.12-.14a.9.9,0,0,0-1.3,0L16,9.26l-.08.16a1.26,1.26,0,0,0,0,.18.6.6,0,0,0,0,.18.91.91,0,0,0,.06.35.94.94,0,0,0,.2.3.81.81,0,0,0,.3.2,1,1,0,0,0,.36.07l.18,0,.18-.06.16-.08a.59.59,0,0,0,.14-.12.81.81,0,0,0,.2-.3.91.91,0,0,0,.07-.35A1.22,1.22,0,0,0,17.69,9.59Z"/>
                <path d="M12.15,5.9l-.06-.18L12,5.57l-.12-.14a.9.9,0,0,0-1.3,0l-.12.14-.08.16a1.26,1.26,0,0,0,0,.18.6.6,0,0,0,0,.18.91.91,0,0,0,.06.35.94.94,0,0,0,.2.3.81.81,0,0,0,.3.2,1,1,0,0,0,.36.07l.18,0,.18-.06.16-.08a.59.59,0,0,0,.14-.12.81.81,0,0,0,.2-.3.91.91,0,0,0,.07-.35A1.22,1.22,0,0,0,12.15,5.9Z"/>
                <path d="M17.52,24.42a.92.92,0,0,0-.25-.86L8.14,14.42a.92.92,0,0,0-1.53.36L2,28.49a.92.92,0,0,0,1.17,1.17l13.7-4.57A.92.92,0,0,0,17.52,24.42Zm-7.43,1L6.29,21.61l.5-1.5,4.81,4.81ZM5.63,23.57l2.5,2.5L4.38,27.32Zm7.92.7L7.44,18.15l.45-1.36,7,7Z"/>
                <path d="M19.25,14.82l.43-2.51,2.51-.43a.92.92,0,0,0,.75-.75l.43-2.51,2.51-.43a.92.92,0,0,0-.31-1.82l-3.15.54a.92.92,0,0,0-.75.75l-.43,2.51-2.51.43a.92.92,0,0,0-.75.75l-.43,2.51L15,14.3a.92.92,0,0,0,.16,1.83h.16l3.15-.54A.92.92,0,0,0,19.25,14.82Z"/>
                <path d="M29.4,15.87l-2.66-1a.92.92,0,0,0-1.13.41l-1,1.79-1.92-.72a.92.92,0,0,0-1.13.41l-1,1.78-1.92-.72A.92.92,0,1,0,18,19.55l2.66,1a.92.92,0,0,0,1.13-.41l1-1.79,1.92.72a.92.92,0,0,0,1.13-.41l1-1.79,1.92.72a.92.92,0,0,0,.65-1.73Z"/>
                <path d="M14.54,11l-.7-1.93,1.79-1a.92.92,0,0,0,.42-1.12L15.36,5l1.79-1a.92.92,0,0,0-.89-1.62L13.78,3.78a.92.92,0,0,0-.42,1.12l.7,1.92-1.79,1a.92.92,0,0,0-.42,1.12l.7,1.93-1.8,1a.92.92,0,1,0,.89,1.62l2.49-1.37A.92.92,0,0,0,14.54,11Z"/>
                <path d="M7.55,9.67a.92.92,0,0,0-.63,1.14l.38,1.31a.92.92,0,0,0,.88.66.91.91,0,0,0,.26,0,.92.92,0,0,0,.63-1.14l-.38-1.31A.92.92,0,0,0,7.55,9.67Z"/>
                <path d="M14.66,19.25a.92.92,0,0,0,.53-.17l.54-.38a.92.92,0,1,0-1.07-1.51l-.54.38a.92.92,0,0,0,.53,1.68Z"/>
                <path d="M10.85,14.52a.92.92,0,0,0,0,1.3l.38.38a.92.92,0,1,0,1.3-1.3l-.38-.38A.92.92,0,0,0,10.85,14.52Z"/>
                <path d="M21,23.85h.16A.92.92,0,0,0,21.27,22L19,21.65a.92.92,0,1,0-.31,1.82Z"/>
            </svg>
        </div>
        <div class="birthdate-show-employees-count" data-active="<?= !empty($arResult['EMPLOYEES']) ? 'true' : 'false' ?>">
            <span>
                <?= count($arResult['EMPLOYEES']) ?>
            </span>
        </div>
        <div class="birthdate-show-partners-count" data-active="<?= !empty($arResult['PARTNERS']) ? 'true' : 'false' ?>">
            <span>
                <?= count($arResult['PARTNERS']) ?>
            </span>
        </div>
    </div>
    <div class="birthdate-show-list" data-role="birthdate-show-list" data-active="false">
        <?php if (!empty($arResult['EMPLOYEES'])) { ?>
            <div class="birthdate-show-list-employees">
                <h2>
                    <?= Loc::getMessage('BIRTHDAY_SHOW_EMPLOYEES') ?>
                </h2>
                <ul>
                    <?php foreach ($arResult['EMPLOYEES'] as $key => $value) { ?>
                        <li>
                            <a href="/company/personal/user/<?= $value['ID'] ?>/">
                                <?= $value['NAME'] ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
        <?php if (!empty($arResult['PARTNERS'])) { ?>
            <div class="birthdate-show-list-partners">
                <h2>
                    <?= Loc::getMessage('BIRTHDAY_SHOW_PARTNERS') ?>
                </h2>
                <ul>
                    <?php foreach ($arResult['PARTNERS'] as $key => $value) { ?>
                        <li>
                            <a href="/b/edp/?deal_id=<?= $value['ID'] ?>/" data-role="birthdate-show-list-link">
                                <?= $value['NAME'] ?>
                            </a>
                            <?php if ($value['STATE']) { ?>
                                <span class="birthdate-show-list-partners-congratulated">
                                    Поздравлен
                                </span>
                            <?php } else { ?>
                                <?php if ((int)$value['MANAGER'] === (int)$user_id || $USER->isAdmin()) { ?>
                                    <a class="birthdate-show-list-partners-congratulate" href="#" data-partner-id="<?= $value['ID'] ?>">
                                        Поздравить
                                    </a>
                                <?php } else { ?>
                                    <span class="birthdate-show-list-partners-congratulate">
                                        Не поздравлен
                                    </span>
                                <?php } ?>
                            <?php } ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
        <?php if (!empty($arResult['NEXT_BIRTHDAYS'])) { ?>
            <div class="birthdate-show-list-next-birthdays">
                <h2>
                    <?= Loc::getMessage('BIRTHDAY_NEXT_BIRTHDAYS') ?>
                </h2>
                <ul>
                    <?php foreach ($arResult['NEXT_BIRTHDAYS'] as $key => $value) { ?>
                        <li>
                            <a href="/company/personal/user/<?= $value['ID'] ?>/">
                                <?= $value['NAME'] ?> - <?= $value['BIRTHDAY'] ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
    </div>
</div>

<script>
    BX.ready(function () {
        let birthdateShowListBlock = document.querySelector('.birthdate-show-wrap');
        let birthdateShowList = document.querySelector('[data-role="birthdate-show-list"]');

        if (birthdateShowList) {
            birthdateShowListBlock.addEventListener('click', (event) => {
                if (event.currentTarget.contains(event.target) || event.target === event.currentTarget) {
                    if (birthdateShowList.dataset.active === 'false') {
                        birthdateShowList.dataset.active = true;
                    } else {
                        birthdateShowList.dataset.active = false;
                    }
                }
            });
        }

        let birthdateShowListLinks = birthdateShowListBlock.querySelectorAll('[data-role="birthdate-show-list-link"]');

        if (birthdateShowListLinks.length > 0) {
            birthdateShowListLinks.forEach(link => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();
                    BX.SidePanel.Instance.open(event.currentTarget.href);
                });
            });
        }

        document.addEventListener('click', (event) => {
            if (event.target === birthdateShowList || birthdateShowList.contains(event.target)) {
                return false;
            } else {
                if (!birthdateShowListBlock.contains(event.target)) {
                    birthdateShowList.dataset.active = false;
                }
            }
        });


        let partnersCongratulate = document.querySelectorAll('.birthdate-show-list-partners-congratulate')

        partnersCongratulate.forEach(el => {
            el.addEventListener('click', (event) => {
                BX.ajax.runComponentAction('vaganov:birthdate.show', 'partnersCongratulate', {
                    mode: 'class',
                    data: {
                        partnerId: el.dataset.partnerId
                    }
                }).then(({data}) => {
                    if (data) {
                        event.target.classList.remove('birthdate-show-list-partners-congratulate');
                        event.target.classList.add('birthdate-show-list-partners-congratulated');
                        event.target.innerHTML = 'Поздравлен';
                    }
                });
            });
        });

    });
</script>