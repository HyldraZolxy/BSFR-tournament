/*
 Author: Hyldra Zolxy (https://www.hyldrazolxy.fr/)
 Description: Global script for all pages
 Version: 1.0.1
 Creation date: 26/05/2023
 Last update: 21/06/2023
*/

/*** Global ***/
const body = document.getElementsByTagName("body")[0];
function timeout(ms: number): Promise<unknown> {
    return new Promise(resolve => setTimeout(resolve, ms));
}

/*** Mobile Nav ***/
const mobileNav = document.getElementById("mobile-nav");
function mobileNavOpen(): void {
    if (mobileNav === null) return;

    if (mobileNav.style.display === "flex") mobileNavClose();
    else {
        mobileNav.style.animation = "var(--trans-nav_mobile-left)";
        mobileNav.style.display = "flex";
    }
}
function mobileNavClose(): void {
    if (mobileNav === null) return;

    mobileNav.style.animation = "var(--trans-nav_mobile-right)";
    timeout(350).then(() => {
        mobileNav.style.display = "none";
    });
}

/*** Collapsible ***/
const collapsibles = document.getElementsByClassName("collapsible");
function collapsiblesOpacity() {
    let active = false;

    for (const collapsible of collapsibles) {
        if (collapsible.classList.contains("active")) {
            active = true;
        }

        collapsible.classList.toggle("opacity-line", !collapsible.classList.contains("active"));
    }

    if (!active) {
        for (const collapsible of collapsibles) {
            collapsible.classList.remove("opacity-line");
        }
    }
}
function collapsiblesListener(): void {
    for (const collapsible of collapsibles) {
        collapsible.addEventListener("click", handleCollapsibleClick);
    }
}
function handleCollapsibleClick(this: HTMLElement): void {
    const contentCollapisble = this.nextElementSibling as HTMLElement;

    this.classList.toggle("active");
    contentCollapisble.style.maxHeight = (contentCollapisble.style.maxHeight) ? "" : contentCollapisble.scrollHeight + "px";

    collapsiblesOpacity();
}
collapsiblesListener();

/*** Collapsible-2 ***/
const collapsibles_secondary = document.getElementsByClassName("collapsible-secondary");
function collapsiblesSecondaryBorder(element: HTMLElement) {
    if (element.classList.contains("collapsible-secondary-line-information-player")) {
        element.style.borderBottom = (element.classList.contains("active")) ? "none" : "1px dashed var(--color-border-line_score)";
    }
}
function collapsiblesSecondaryOpacity() {
    let active = false;

    for (const collapsible of collapsibles_secondary) {
        if (collapsible.classList.contains("active")) {
            active = true;
        }

        collapsible.classList.toggle("opacity-line", !collapsible.classList.contains("active"));
    }

    if (!active) {
        for (const collapsible of collapsibles_secondary) {
            collapsible.classList.remove("opacity-line");
        }
    }
}
function collapsiblesSecondaryListener(): void {
    for (const collapsible of collapsibles_secondary) {
        collapsible.addEventListener("click", handleCollapsibleSecondaryClick);
    }
}
function handleCollapsibleSecondaryClick(this: HTMLElement): void {
    const contentCollapsible_secondary = this.nextElementSibling as HTMLElement;
    const parentCollapsible_secondary  = this.parentElement as HTMLElement;

    this.classList.toggle("active");
    if (contentCollapsible_secondary.style.maxHeight) {
        contentCollapsible_secondary.style.maxHeight = "";
        parentCollapsible_secondary.style.maxHeight  = (parentCollapsible_secondary.scrollHeight - contentCollapsible_secondary.scrollHeight) + "px"
    } else {
        contentCollapsible_secondary.style.maxHeight = contentCollapsible_secondary.scrollHeight + "px";
        parentCollapsible_secondary.style.maxHeight  = (parentCollapsible_secondary.scrollHeight + contentCollapsible_secondary.scrollHeight) + "px"
    }

    collapsiblesSecondaryOpacity();
    collapsiblesSecondaryBorder(this);
}
collapsiblesSecondaryListener();