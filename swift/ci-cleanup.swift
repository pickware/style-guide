#!/usr/bin/swift

// Disable some swiftlint rules, which are too annoying when programming for the CLI
// swiftlint:disable explicit_acl prefixed_toplevel_constant

import Foundation

let swiftLintConfigUrl = URL(fileURLWithPath: ".swiftlint.yml")
let swiftLintConfigBackupUrl = URL(fileURLWithPath: ".swiftlint.yml.bak")

func removeIfExists(atPath path: String) throws {
    if FileManager.default.fileExists(atPath: path) {
        print("Removing \(path)")
        try FileManager.default.removeItem(atPath: path)
    }
}

try removeIfExists(atPath: "./.bundle")
try removeIfExists(atPath: swiftLintConfigUrl.relativePath)
try removeIfExists(atPath: "./Frameworks/.swiftlint.yml")
try FileManager.default.moveItem(at: swiftLintConfigBackupUrl, to: swiftLintConfigUrl)
