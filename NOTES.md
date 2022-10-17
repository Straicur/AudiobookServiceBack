# Notes

## Raw Specification

1. Users
    1. Authorization
    2. User Data Management
        1. Add, Edit, Delete, Visibility
        2. RODO
    3. Types
        1. Administrator
        2. InsidePark
        3. User
            1. Landlord (Wynajmujący)
                1. Osoba fizyczna
                2. JDG
                3. Spółka
            2. Tenant (Najemca)
            3. Investor (Szuka okazji)
            4. Servicer
        4. Guest
    4. User Groups
2. Logs
3. Reserving (Proces od wyszukania do podpisania umowy)
4. Renting (Po skończeniu negocjacji i podpisaniu umowy)
    1. Opłaty
5. Scoring
6. Advertising
7. Investing (Kupno należności)
8. Services
9. Trainings (Moodle ?)
10. File Module

## Questions

1. Jakie flagi mają mieszkania w wyszukiwarce ?
2. Czy oferta zawiera linki do jakiś zewnętrznych mediów (np. YouTube) ?
3. Jaka jest różnica między flagami (for_family) a amenities (big balcony) ?

# Hierarchia InsidePark

Czy InsidePark mają hierarchię ról ? (Manager, Servicer, Investor) ?

```text
InsideParkManager -> InsideParkServicer
                  -> InsideParkInvestor
```