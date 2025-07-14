function getModuleName() {
    const currentPath = new URL(import.meta.url).pathname;
    const pathSegments = currentPath.split('/');
    const modulesIndex = pathSegments.findIndex(segment => segment === 'Modules');
    return modulesIndex !== -1 && modulesIndex + 1 < pathSegments.length
        ? pathSegments[modulesIndex + 1]
        : '';
}

const dirName = 'Modules/' + getModuleName();

export const paths = [];
