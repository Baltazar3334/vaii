# QuizMaker - Semestrálna práca VAII

Aplikácia na tvorbu, správu a hranie interaktívnych kvízov. Systém umožňuje používateľom registrovať sa, vytvárať vlastné kvízy s viacerými otázkami a hrať kvízy vytvorené komunitou.

## Inštalácia a spustenie

### Prerekvizity
- **Docker** a **Docker Compose**
- **Node.js** (verzia 20+) a **npm**

### Postup spustenia
1. **Spustenie Backend-u (Docker):**
   V koreňovom priečinku projektu spustite príkaz:
   ```sh
   docker-compose up -d
   ```
   Tento príkaz spustí MySQL databázu (port 3306) a PHP server (port 8000). Databáza sa automaticky zinicializuje súborom `backend/init.sql`.

2. **Spustenie Frontend-u (Vite):**
   V novom termináli vykonajte:
   ```sh
   npm install
   npm run dev
   ```
   Aplikácia bude dostupná na adrese `http://localhost:5173` (alebo podľa výpisu v termináli).

## 📊 Splnenie požiadaviek zadania

Aplikácia spĺňa všetky povinné kritériá:

- **Oddelenie vrstiev:** Striktné rozdelenie na prezentačnú časť (Vue.js) a aplikačnú logiku (PHP Controllers).
- **Dynamické stránky (5):** Domov, Prihlásenie/Registrácia, Hranie kvízu, Profil používateľa, Nastavenia.
- **Databázové entity (3+Users):**
    1. `quizzes` (hlavička kvízu)
    2. `questions` (otázky)
    3. `question_options` (možnosti odpovedí)
       *Pozn: Tabuľka `users` sa do počtu 3 entít neráta.*
- **Vzťahy v DB:** Implementované vzťahy 1:N (Kvíz -> Otázky) a 1:N (Otázka -> Odpovede).
- **CRUD operácie:** Plne implementované nad entitou `quizzes` (Vytvorenie, Čítanie, Úprava, Mazanie) aj `questions`.
- **Bezpečnosť:**
    - Heslá sú hashované pomocou `password_hash()`.
    - Ochrana proti SQL Injection pomocou PDO Prepared Statements.
    - Endpoindy v `api.php` overujú existenciu session pri chránených akciách.
- **Responzivita:** Použitie CSS Grid a Flexboxu s Media Queries pre plnú funkčnosť na mobilných zariadeniach.
- **Technológie:** Vue 3 (Composition API), PHP 8.2 (OOP prístup), MySQL, Docker.

*Vypracované s podporou nástrojov generatívnej AI pri návrhu CSS layoutu a typeScript kódom*
