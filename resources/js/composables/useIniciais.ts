export function getIniciais(nomeCompleto?: string): string {
    if (!nomeCompleto) return '';

    const nomes = nomeCompleto.trim().split(' ');

    if (nomes.length === 0) return '';
    if (nomes.length === 1) return nomes[0].charAt(0).toUpperCase();

    return `${nomes[0].charAt(0)}${nomes[nomes.length - 1].charAt(0)}`.toUpperCase();
}

export function useIniciais() {
    return { getIniciais };
}