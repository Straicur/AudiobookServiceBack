# AudiobookServiceBack
Jest to backend aplikacji do zarządzania audiobookami.
<br>

# Opis
## Admin 
Administrator ma możliwość dodawania nowych kategorii i przypisywania do nich audiobooków, audiobooki natomiast dodaje w odpowiednim formacie pliku zip a następnie może nimi zarządzać (odsłuch, pobranie iformacji oraz ich edycja, ponowne przesłanie, usunięcie i dodanie kategorii oraz usunięcie audioobooka z kategorii oraz systemu). Zarządzając użytkownikami może im zmieniać chociażby: role, telefon, hasło i aktywować ich. Dodatkową opcją dla użytkwonika jest prośba o usunięcie konta, którą również rozpatruje administrator. Ostatnią funkcjonalności jest dodawanie powiadomień np. wygenerowanie co tygodniowej listy proponowanych, dodanie nowego audiobooka lub kategorii.
## User
Użytkownik na początku otrzymuje listę wsyzstkich audiobooków z podziałem na kategorie oraz listę proponowanych (ustalana na podstawie lubioanych kategorii). Po pobraniu detali audiobooka ma możliwość odsłuchu z wszystkimi udogodnieniami i dodania/usunięcia z mojej listy. Oczywiście moja lista jest to lista szybkiego dostępu do ulubionych audiobooków. Może również zarządzać swoim kontem w ograniczonym ale wystarczającym stopniu, może np: zmienić hasło i email, informacje takie jak imie czy telefon oraz wysłać prośbę o usunięcie konta. Dodatkowo otrzymuje powiadomienia systemowe.
<br>

## Podział aplikacji z funkcjonalnościami 
<ol>
  <li>Administrator
    <ul>
      <li>Zarządzanie kategoriami</li>
      <li>Zarządzanie audiobookami</li>
      <li>Zarządzanie użytkownikami</li>
      <li>Zarządzanie powiadomieniami</li>
    </ul>
  </li>
  <li>User
    <ul>
      <li>Możliwość odsłuchu audiobooka</li>
      <li>Możliwość dodania do mojej listy</li>
      <li>Możliwość zmiany ustawień konta</li>
      <li>Otrzymywanie powiadomień</li>
      <li>Rejestracja i logowanie</li>
      <li>Odzyskiwanie hasła</li>
    </ul>
  </li>
<ol>
