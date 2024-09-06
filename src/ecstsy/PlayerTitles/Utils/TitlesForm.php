<?php

namespace ecstsy\PlayerTitles\Utils;

use ecstsy\PlayerTitles\Loader;
use pocketmine\player\Player;
use Vecnavium\FormsUI\SimpleForm;
use pocketmine\utils\TextFormat as C;

class TitlesForm {

    public static function getTitleForm(Player $player, int $page = 1): SimpleForm {
        $session = Loader::getPlayerManager()->getSession($player);
        $config = Utils::getConfiguration("config.yml");

        $form = new SimpleForm(function (Player $player, $data) use ($session, $config, $page): void {
            if ($data === null) {
                return;
            }

            if ($data === 'next') {
                $player->sendForm(TitlesForm::getTitleForm($player, $page + 1));
            } elseif ($data === 'prev') {
                $player->sendForm(TitlesForm::getTitleForm($player, $page - 1));
            } else {
                $titlesPerPage = $config->getNested("settings.titles-per-page", 5);
                $selectedTitleIndex = ($page - 1) * $titlesPerPage + $data;

                $titleKeys = array_keys($config->get("titles"));
                if (isset($titleKeys[$selectedTitleIndex])) {
                    $selectedTitleKey = $titleKeys[$selectedTitleIndex];
                    $selectedTitleDisplay = $config->getNested("titles.$selectedTitleKey.display");
                    $permission = "playertitles.title.$selectedTitleKey";

                    if ($player->hasPermission($permission)) {
                        $message = $config->getNested("settings.message");
                        if (is_array($message)) {
                            $message = implode("\n", $message);
                        }

                        $formattedMessage = str_replace("{title}", $selectedTitleDisplay, $message);
                        $player->sendMessage(C::colorize($formattedMessage));
                        $session->setTitle(C::colorize($selectedTitleDisplay));
                    } else {
                        $noPermissionMessage = $config->getNested("settings.no-title-perm", "&cYou do not have permission to use this title!");
                        $player->sendMessage(C::colorize($noPermissionMessage));
                    }
                }
            }
        });

        $titlesPerPage = $config->getNested("settings.titles-per-page", 5);
        $titleKeys = array_keys($config->get("titles"));
        $start = ($page - 1) * $titlesPerPage;
        $end = min($start + $titlesPerPage, count($titleKeys));

        for ($i = $start; $i < $end; $i++) {
            $titleKey = $titleKeys[$i];
            $titleData = $config->getNested("titles.$titleKey");
            $status = Utils::getPermissionLockedStatus($player, "playertitles.title.$titleKey");

            $form->addButton(C::colorize($titleData['display']) . "\n" . $status);
        }

        if ($page > 1) {
            $form->addButton(C::colorize($config->getNested("forms.titles.back", "&r&8Previous Page")), -1, '', 'prev');
        }

        if ($end < count($titleKeys)) {
            $form->addButton(C::colorize($config->getNested("forms.titles.next", "&r&8Next Page")), -1, '', 'next');
        }

        $form->setTitle(C::colorize($config->getNested("forms.titles.name", "&r&8Titles")));
        return $form;
    }
}