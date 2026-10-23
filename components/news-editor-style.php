<style>
    .bottom-buttons {
        z-index: 90;
    }

    .connections .collapse-panel {
        margin-bottom: 2rem;
    }

    .connections .collapse-content {
        background-color: white;
    }

    .connections .collapse-header {
        cursor: pointer;
        font-size: 1.8rem;
        font-weight: bold;
    }

    .connections .collapse-header,
    .connections .collapse-panel[open] .collapse-header {
        color: var(--primary-color);
        border-color: var(--primary-color);
        background-color: var(--primary-color-20);
    }
    .connections .collapse-header:hover,
    .connections .collapse-panel[open] .collapse-header:hover {
        background-color: var(--primary-color-30);
    }

    .collapse-panel.project-panel {
        --primary-color: #1c7d72;
        --primary-color-dark: #157065;
        --primary-color-20: #1c7d7233;
        --primary-color-30: #1c7d723d;
    }

    .collapse-panel.infrastructure-panel {
        --primary-color: #d48646;
        --primary-color-dark: #b36a36;
        --primary-color-20: #d4864633;
        --primary-color-30: #d486463d;
    }

    .collapse-panel.activity-panel {
        --primary-color: #9f4371;
        --primary-color-dark: #7a345a;
        --primary-color-20: #9f437133;
        --primary-color-30: #9f43713d;
    }
    .collapse-panel.event-panel {
        --primary-color: #4a90e2;
        --primary-color-dark: #3a6fb5;
        --primary-color-20: #4a90e233;
        --primary-color-30: #4a90e23d;
    }

    .collapse-panel.person-panel {
        --primary-color: #8e81d0;
        --primary-color-dark: #6b5eb3;
        --primary-color-20: #8e81d033;
        --primary-color-30: #8e81d03d;
    }

    .connections .collapse-header span {
        margin-left: auto;
        align-items: center;
        font-size: 1.4rem;
    }


    .featured-editor {
        border: var(--border-width) solid var(--primary-color);
        border-left-width: .5rem;
        border-radius: var(--border-radius);
        background-color: var(--primary-color-20);
        padding: 1.5rem;
    }

    .featured-editor.empty {
        border-color: var(--border-color);
        background-color: var(--muted-color-very-light);
    }

    .featured-editor .featured-title {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-top: 0;
        color: var(--primary-color);
    }

    .featured-editor.empty .featured-title {
        color: var(--muted-color);
    }

    .character-count {
        display: block;
        text-align: right;
        color: var(--muted-color);
    }


    .suggestions {
        color: #464646;
        /* position: absolute; */
        margin: 10px auto;
        top: 100%;
        left: 0;
        height: 19.2rem;
        overflow: auto;
        bottom: -3px;
        width: 100%;
        box-sizing: border-box;
        min-width: 12rem;
        background-color: white;
        border: var(--border-width) solid #afafaf;
        /* visibility: hidden; */
        /* opacity: 0; */
        z-index: 100;
        -webkit-transition: opacity 0.4s linear;
        transition: opacity 0.4s linear;
        border-radius: var(--border-radius);
    }

    .suggestions a {
        display: block;
        padding: 0.5rem;
        border-bottom: var(--border-width) solid #afafaf;
        color: #464646;
        text-decoration: none;
        width: 100%;
    }

    .suggestions a:hover {
        background-color: #f0f0f0;
    }


</style>
