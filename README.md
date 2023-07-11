# AudiobookServiceBack
  AudiobookServiceBack to backend aplikacji do zarządzania audiobookami. Jest to Rest Api, które odpowiada na zapytania z frontendu. To mój projekt portfolio, który zawiera dużo przykładów wykorzystania frameworka Symfony, w którym chcę się rozwijać i specjalozować. Wszystkie pliki zapisywane są na serwerze w podanej ścieżcę (ma zabezpieczenie przekroczenia rozmiaru na dysku). Authentykacja możliwa jest poprzez wygenerowany token, który należy dodać w header przy prawie każdym zapytaniu. Zapytania przesyłane są w postaci json, aplikacja je odpowiednio serializuje do obiektów i działa na klasach, nie na czystym jsonie. Błędy podlegają rejestrowaniu za pomocą pakietu monolog oraz w razie wystąpienia jakiegokolwiek, zwracany jest odpowiedni response 500, 400, 401, 403. Do odpowiedniego działania audiobooka musi on zostać podany w odpowienim formacie zip (plików mp3 oraz cover jpg lub png). Gdy wystąpi błąd, może on zostać usunięty z dysku, a przed aktywacją nie jest widoczny dla użytkowników. Dotyczy to również całej kategorii oraz podpiętych do niej audiobooków. Dodatkowym zabezpieczeniem, poza samym Uuid dla dostępu do danych przesyłanych z api (detale audiobooków), wymagany jest odpowiedni klucz kategori, tworzony razem z nią. Dodane zostały również tłumaczenia, które w zależności od przesłanego w headerze języka lub lokalizacji, zwracają użytkownikowi wiadomość w odpowienim języku (aktualnie polski i angielski). Dodane zostały również testy, które sprawdzają i wyłapują zwracane odpowiednio błędy. Dzięki dodaniu pakietu make można wykonywać komendy shelowe, które zostały przygotowane do szybszego ustawienia serwera oraz dodania podstawowych danych. Wykonują one w większości przygotowane komendy symfony i można je znależć w pliku Makefile. Oczywiście dodany został też serwer smpt, który wysyła odpowienie dla danej sytuacji maile, które również są tłumaczone. Zawarte zostały tam dodatkowo narzędzia 
<br>

# Opis
## Admin
Administrator ma możliwość dodawania nowych kategorii i przypisywania do nich audiobooków, audiobooki natomiast dodaje w odpowiednim formacie pliku zip, który składa się z plików mp3 oraz cover jpg lub png, a następnie może nimi zarządzać (odsłuch, pobranie iformacji oraz ich edycja, ponowne przesłanie, usunięcie i dodanie kategorii oraz usunięcie audioobooka z kategorii oraz systemu). Zarządzając użytkownikami może im zmieniać chociażby: role, telefon, hasło i aktywować ich. Dodatkową opcją dla użytkwonika jest prośba o usunięcie konta, którą również rozpatruje administrator. Ostatnią funkcjonalności jest dodawanie powiadomień np. wygenerowanie co tygodniowej listy proponowanych, dodanie nowego audiobooka lub kategorii.

## User
Użytkownik na początku otrzymuje listę wsyzstkich audiobooków z podziałem na kategorie oraz listę proponowanych (ustalana na podstawie lubioanych kategorii). Po odsłuchu odpowiedniej ilości audiobooków pojawi się lista proponowanych na ich podstawie. Po pobraniu detali audiobooka ma możliwość odsłuchu z wszystkimi udogodnieniami jak komentarze oraz ich likowanie, ocena audibooka po przesłuchaniu minimum połowy i dodania/usunięcia z mojej listy. Oczywiście moja lista jest to lista szybkiego dostępu do ulubionych audiobooków. Może również zarządzać swoim kontem w ograniczonym ale wystarczającym stopniu, może np: zmienić hasło i email, informacje takie jak imie czy telefon oraz wysłać prośbę o usunięcie konta. Dodatkowo otrzymuje powiadomienia systemowe. Zostały dodane dodatkowo takiego strony jak O nas, polityka prywatności oraz odnośniki do tej dokumentacji oraz rest Api z którego korzysta.
