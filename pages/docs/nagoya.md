
flowchart TD

%% --- Grundstruktur ---

A0([New Project proposal]) --> Z2{Collect/Obtain/Utilize<br>from outside Germany?}
Z2 -- Yes --> A1[incomplete<br><small>→ Researchers add Countries</small>]
Z2 -- No --> Z1[not-relevant<br><small>→ no further actions</small>]
A1 -- all countries added --> B1[abs-review<br><small>→ ABS-Team reviews countries</small>]
B1 -->|all countries reviewed | C1{abs=true present?}

C1 -- No --> S0[not-relevant<br><small>→ no further actions</small>]
C1 -- Yes --> D1[researcher-input<br><small>→ Researchers complete scope<br>geo/temporal/material/utilization</small>]
D1 -->|Scope complete| E1[awaiting-abs-evaluation<br><small>→ ABS-Team evaluates & classifies</small>]

E1 -->|Result A| F1[in-scope-eu A<br><small>→ permits may be required</small>]
E1 -->|Result B| F2[in-scope-national B<br><small>→ permits may be required</small>]
E1 -->|Result C| F3[out-of-scope C<br><small>→ no further actions</small>]

F1 & F2 -->|not all permits granted| G1[permits-pending<br><small>→ Researchers & ABS-Team manage permits</small>]
G1 -->|all permits granted| H1[compliant<br><small>→ completed</small>]

%% --- Farben/Rollen ---
classDef researcher fill:#E3F2FD,stroke:#64B5F6,color:#000;
classDef abs fill:#FBE9E7,stroke:#EF6C00,color:#000;
classDef neutral fill:#E8EAF6,stroke:#9FA8DA,color:#000;

class A1,D1 researcher
class B1,E1,F1,F2 abs
class S0,F3,H1,Z1 neutral
class G1 researcher,abs