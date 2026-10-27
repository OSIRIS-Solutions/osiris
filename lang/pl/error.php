<?php

return [
    'admin_no_permission' => 'Nie masz uprawnień dostępu do obszaru administracyjnego.',
    'file_partially_uploaded' => 'Przesłany plik został przesłany tylko częściowo.',
    'file_too_big_max_2MB' => 'Plik jest zbyt duży: dopuszczalny maksymalny rozmiar to 2 MB.',
    'file_upload_generic' => 'Przepraszamy, wystąpił błąd podczas przesyłania pliku.',
    'file_upload_exceeds_limit' => 'Przesłany plik przekracza wartość dyrektywy upload_max_filesize w pliku php.ini',
    'file_upload_missing_temp' => 'Brakuje folderu tymczasowego.',
    'file_upload_stopped' => 'Rozszerzenie PHP zatrzymało przesyłanie pliku.',
    'file_upload_too_large' => 'Plik jest zbyt duży: dopuszczalny maksymalny rozmiar to {{max}}.',
    'file_upload_write_failed' => 'Nie udało się zapisać pliku na dysku.',
    'method_not_allowed_message' => 'Metoda „{{method}}” nie jest dozwolona dla podanego adresu URL.',
    'method_not_allowed' => 'Metoda niedozwolona',
    'no_file_uploaded' => 'Nie przesłano żadnego pliku.',
    'no_values' => 'Nie podano żadnych wartości.',
    'organization_select_missing' => 'Nie wybrano organizacji',
    'page_not_found_message' => 'Strona, której szukasz, nie istnieje lub została przeniesiona.',
    'page_not_found' => 'Strona nie została znaleziona',
    'password_no_permission' => 'Nie masz uprawnień do resetowania haseł.',
    'something_went_wrong' => 'Wystąpił błąd.',
    'user_not_found' => 'Nie znaleziono użytkownika.',
    'username_already_taken' => 'Ta nazwa użytkownika jest już zajęta. Spróbuj ponownie.',
    'news_not_enabled' => 'Funkcja wiadomości nie jest włączona.',
    'infrastructure_no_edit_permission' => 'Nie masz uprawnień do edycji tej infrastruktury.',
    'why_do_i_have_to_confirm_my_authorships' => 'Dlaczego muszę potwierdzić swoje autorstwo?',
    'why_do_i_have_to_review_online_ahead_of_print_articles' => 'Dlaczego muszę recenzować artykuły <q>online przed wydaniem drukowanym</q>?',
    'online_ahead_of_print_means_that_the_publication_is_already_available_onlin' => '
                    [Online ahead of print] oznacza, że publikacja jest już dostępna w Internecie, ale jej faktyczna publikacja w numerze czasopisma jest jeszcze w toku. Przykładem jest numer bazy danych NAR, w którym publikacje są już dostępne online we wrześniu lub październiku, mimo że numer ukaże się dopiero w styczniu następnego roku.
                    ',
    'these_publications_cannot_be_included_in_the_reports_they_are_included_in_o' => '
                    <b>
                    Publikacje te nie mogą być uwzględniane w raportach.</b>
                    Zostały one umieszczone w OSIRIS
                    , aby nie stracić ich z oczu oraz ponieważ stanowią one już osiągnięte wyniki. Z tego powodu regularnie pojawia się pytanie, czy dana publikacja została już opublikowana. Tylko wtedy można ją uwzględnić w raportowaniu.
                    ',
    'why_do_i_have_to_review_these_activities' => 'Dlaczego muszę sprawdzać te zadania?',
    'in_order_to_ensure_that_the_correct_status_and_completion_date_is_always_in' => '
                    
                    Aby zapewnić, że dla działań posiadających status zawsze wskazywany jest prawidłowy status i data zakończenia, element OSIRIS
                    wyświetli ostrzeżenie, jeśli dane zadanie nadal ma status „w przygotowaniu”, mimo że data rozpoczęcia przypada w przeszłości, lub „w toku”, mimo że data zakończenia przypada w przeszłości.
                    <b>
                    Sprawdź, czy praca została już ukończona.</b>
                    Jeśli tak, podaj, czy praca została pomyślnie ukończona, czy nie, oraz podaj prawidłową datę zakończenia. Jeśli praca jest nadal „w przygotowaniu”, przedłuż termin, wprowadzając nową przewidywaną datę zakończenia.
                    Jeśli praca jest nadal „w trakcie realizacji”, przedłuż okres, wprowadzając nową przewidywaną datę zakończenia.
                    OSIRIS
                    w odpowiednim czasie ponownie zapyta, czy praca została pomyślnie ukończona.
                    ',
    'no_warnings' => 'Brak ostrzeżeń',
    'please_review_the_following_activities_that_were_rejected_in_the_quality_wo' => 'Proszę zapoznać się z poniższymi zadaniami, które zostały odrzucone w ramach procesu kontroli jakości:',
    'what_does_it_mean' => 'Co to oznacza?',
    'reason_for_rejection' => 'Powód odrzucenia',
    'edit_activity' => 'Edytuj działanie',
    'reply' => 'Odpowiedz',
    'please_review_the_following_authorships' => 'Proszę zapoznać się z poniższymi informacjami o autorstwie:',
    'approve_all' => 'Zatwierdź wszystko',
    'please_confirm_possibly_again_that_you_are_the_author_and_all_details_are_c' => 'Proszę potwierdzić (ewentualnie ponownie), że są Państwo autorem i że wszystkie dane są poprawne: ',
    'is_this_your_activity' => 'Czy to jest Twoje zadanie?',
    'no_this_is_not_me' => 'Nie, to nie ja',
    'status_at_this_time_point' => 'Stan w tym momencie',
    'please_review_the_following_online_ahead_of_print_articles' => 'Proszę zapoznać się z następującymi artykułami <q>opublikowanymi online przed wydaniem drukowanym</q>:',
    'this_publication_is_marked_as_online_ahead_of_print_is_it_still_not_officia' => 'Ta publikacja jest oznaczona jako <q>Wersja online przed wydaniem drukowanym</q>. Czy nadal nie została oficjalnie opublikowana?',
    'yes_still_online_ahead_of_print_ask_again_later' => 'Tak, nadal <q>Wersja online przed wydaniem drukowanym</q> (zapytaj ponownie później).',
    'no_longer_online_ahead_of_print_review' => 'Już niedostępne <q>Wersja online przed wydaniem drukowanym</q> (Recenzja)',
    'please_review_the_status_of_the_following_activities' => 'Proszę sprawdzić status następujących działań:',
    'the_activity_has_ended_but_the_status_is_still_in_progress_please_confirm_i' => 'Zadanie zostało zakończone, ale jego status nadal wynosi <b>w toku</b>. Proszę potwierdzić, czy praca została pomyślnie zakończona, czy też nie, albo przedłużyć termin wykonania.',
    'the_activity_has_officially_started_but_the_status_is_still_in_preparation' => 'Zadanie zostało oficjalnie rozpoczęte, ale jego status nadal wynosi <b>w przygotowaniu</b>. Proszę zmienić status lub przesunąć ramy czasowe.',
    'ended_at_extend_until' => 'Zakończono w / Rozszerzyć do',
    'in_preparation' => 'W przygotowaniu',
    'in_progress' => 'W trakcie realizacji',
    'aborted' => 'Przerwano',
    'do_you_still_work_on_the_following_activities' => 'Czy nadal pracujesz nad następującymi zadaniami?',
    'yes_still_running' => 'Tak, nadal działa',
    'no_edit' => 'Nie (Edytuj)',
    'please_have_a_look_at_the_following_projects' => 'Zapoznaj się z następującymi projektami:',
    'the_project' => 'Projekt',
    'still_has_the_status_applied_is_this_correct' => 'nadal ma status <q>zastosowano</q>. Czy to jest poprawne? ',
    'yes_ask_again_later' => 'Tak, zapytaj ponownie później',
    'has_ended_you_can_either_prolong_it_or_end_it' => 'zakończył się. Możesz go przedłużyć lub zakończyć:',
    'please_have_a_look_at_the_following_infrastructures' => 'Proszę zapoznać się z następującymi elementami infrastruktury:',
    'please_update_the_statistics_of' => 'Proszę zaktualizować statystyki dotyczące ',
    'from' => 'z ',
    'update_now' => 'Zaktualizuj teraz',
    'please_review_the_following_nagoya_protocol_submissions' => 'Proszę zapoznać się z następującymi zgłoszeniami dotyczącymi Protokołu z Nagoi:',
    'the_nagoya_protocol_compliance_for_project' => 'Zgodność projektu z protokołem z Nagoi',
    'requires_your_input' => 'Wymaga wprowadzenia danych przez użytkownika.',
    'provide_input_now' => 'Prześlij tekst wejściowy teraz',
    'view_project' => 'Wyświetl projekt',
    'sometimes_other_scientists_or_members_of_the_institute_add_scientific_activ' => '
                    Czasami inni naukowcy lub pracownicy instytutu dodają działania naukowe, w których również brałeś udział. System próbuje je przypisać automatycznie, dlatego pojawiają się one na tej liście. Jednak w tym procesie może dojść do wielu błędów. Dla celów sprawozdawczych ważne jest nie tylko, aby dane bibliograficzne były poprawne, ale również, aby użytkownicy byli prawidłowo przypisani. Dlatego ważne jest, <b>czy to w ogóle jesteś ty</b>, czy może ktoś o podobnym nazwisku, aby twoje <b>nazwisko było napisane poprawnie</b> oraz abyś był również <b>związany z {{affiliation}}</b>.
                    ',
    'i_confirm_that_i_am_the_author_of_all_of_the_following_publications_and_tha' => 'Potwierdzam, że jestem autorem <b>wszystkich/</b> poniższych publikacji oraz że moją afiliacją zawsze była {{affiliation}}.',
    'yes_and_i_was_affiliated_to_the_affiliation' => 'Tak, i byłem związany z {{affiliation}}',
    'yes_but_i_was_not_affiliated_to_the_affiliation' => 'Tak, ale nie byłem powiązany z {{affiliation}}',
];
