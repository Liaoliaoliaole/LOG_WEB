import { IsoStandard } from './iso-standard-model';

export interface SensorLinkModalSubmitModel {
    isoStandard: IsoStandard;
    action: SensorLinkModalSubmitAction;
}

export interface SensorLinkModalInitiateModel {
    anchor: string;
    unit: string;
    configuredIsoCodes: string[];
    unlinked: boolean;
    existingIsoStandard?: IsoStandard;
}

export enum SensorLinkModalSubmitAction {
    Remove,
    Update,
    Add
}
