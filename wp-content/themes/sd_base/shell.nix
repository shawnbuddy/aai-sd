# shell.nix
{ pkgs ? import <nixpkgs> {} }:

pkgs.mkShell {
  buildInputs = [
    pkgs.nodejs_20
    pkgs.yarn # or pkgs.pnpm or pkgs.nodePackages.npm
  ];
}

